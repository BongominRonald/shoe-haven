<?php

namespace Tests\Feature;

use App\Models\InventoryHistory;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Models\UserRole;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\Feature\Concerns\CreatesShopData;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;
    use CreatesShopData;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    private function makeAdmin(): User
    {
        $admin = User::factory()->create();
        UserRole::create(['user_id' => $admin->id, 'role' => 'admin']);

        return $admin;
    }

    private function makeUser(): User
    {
        return User::factory()->create();
    }

    private function makeOrder(array $overrides = [], ?Carbon $createdAt = null): Order
    {
        $user = $overrides['user'] ?? User::factory()->create();

        $order = Order::create([
            'user_id' => $user->id,
            'total_amount' => $overrides['total_amount'] ?? 100000,
            'payment_method' => $overrides['payment_method'] ?? 'mobile_money',
            'payment_phone' => $overrides['payment_phone'] ?? '0700000000',
            'status' => $overrides['status'] ?? 'delivered',
            'payment_status' => $overrides['payment_status'] ?? 'paid',
            'transaction_id' => $overrides['transaction_id'] ?? 'TXN-' . uniqid(),
        ]);

        if ($createdAt) {
            $order->forceFill(['created_at' => $createdAt])->save();
        }

        if (isset($overrides['items'])) {
            foreach ($overrides['items'] as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'] ?? 1,
                    'price_at_sale' => $item['price_at_sale'] ?? 50000,
                ]);
            }
        }

        return $order;
    }

    public function test_guest_is_redirected_from_dashboard(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
    }

    public function test_non_admin_cannot_view_dashboard(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user)->get(route('admin.dashboard'))->assertForbidden();
    }

    public function test_dashboard_loads_with_empty_data(): void
    {
        $this->makeProductWithStock([], 10);

        $this->actingAs($this->makeAdmin())
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Store Overview')
            ->assertSee('Revenue')
            ->assertSee('Orders')
            ->assertSee('New customers')
            ->assertSee('Products')
            ->assertSee('Pending orders')
            ->assertSee('Low stock')
            ->assertSee('id="salesChart"', false)
            ->assertSee('Attention required')
            ->assertSee('Recent orders')
            ->assertSee('Best sellers')
            ->assertSee('Inventory overview')
            ->assertSee('Customer overview')
            ->assertSee('Recent activity');
    }

    public function test_revenue_kpi_shows_formatted_amount(): void
    {
        $this->makeOrder(['total_amount' => 654321], Carbon::now());

        $this->actingAs($this->makeAdmin())
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('654,321');
    }

    public function test_revenue_excludes_cancelled_orders(): void
    {
        $this->makeOrder(['total_amount' => 500000], Carbon::now());
        $this->makeOrder(['total_amount' => 999999, 'status' => 'cancelled'], Carbon::now());

        $this->actingAs($this->makeAdmin())
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('500K')
            ->assertDontSee('1.5M');
    }

    public function test_orders_kpi_counts_orders_in_range(): void
    {
        $this->makeOrder([], Carbon::now());
        $this->makeOrder([], Carbon::today()->subDays(2));
        $this->makeOrder([], Carbon::today()->subDays(10));

        $this->actingAs($this->makeAdmin())
            ->get(route('admin.dashboard'))
            ->assertOk();

        $response = $this->actingAs($this->makeAdmin())
            ->get(route('admin.dashboard', ['range' => '7d']))
            ->assertOk();

        $response->assertSee('Last 7 days');
        $this->assertStringContainsString('data-revenue=', $response->getContent());
    }

    public function test_custom_date_range_is_accepted(): void
    {
        $this->makeOrder([], Carbon::parse('2026-01-05'));
        $this->makeOrder([], Carbon::parse('2026-01-20'));

        $this->actingAs($this->makeAdmin())
            ->get(route('admin.dashboard', ['range' => 'custom', 'from' => '2026-01-01', 'to' => '2026-01-10']))
            ->assertOk()
            ->assertSee('Custom range');
    }

    public function test_attention_shows_out_of_stock_product(): void
    {
        $this->makeProductWithStock([], 0);

        $this->actingAs($this->makeAdmin())
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('out of stock', false)
            ->assertSee('HIGH');
    }

    public function test_attention_shows_low_stock_product(): void
    {
        $this->makeProductWithStock([], 2);

        $this->actingAs($this->makeAdmin())
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('running low', false)
            ->assertSee('MEDIUM');
    }

    public function test_attention_shows_failed_payments_with_link(): void
    {
        $this->makeOrder(['payment_status' => 'failed']);

        $this->actingAs($this->makeAdmin())
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('failed payment', false)
            ->assertSee('payment_status=failed', false);
    }

    public function test_recent_orders_table_lists_latest_orders(): void
    {
        $order = $this->makeOrder(['total_amount' => 123000], Carbon::now()->subMinutes(5));

        $this->actingAs($this->makeAdmin())
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('#' . str_pad((string) $order->id, 4, '0', STR_PAD_LEFT))
            ->assertSee('123,000');
    }

    public function test_best_sellers_exclude_cancelled_orders(): void
    {
        $product = $this->makeProductWithStock([]);

        $this->makeOrder([
            'status' => 'delivered',
            'items' => [['product_id' => $product->id, 'quantity' => 4, 'price_at_sale' => 50000]],
        ], Carbon::now());
        $this->makeOrder([
            'status' => 'cancelled',
            'items' => [['product_id' => $product->id, 'quantity' => 40, 'price_at_sale' => 50000]],
        ], Carbon::now());

        $this->actingAs($this->makeAdmin())
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('4 sold', false)
            ->assertDontSee('40 sold', false);
    }

    public function test_inventory_widget_shows_totals_and_size_stock(): void
    {
        $this->makeProductWithStock([], 10);

        $this->actingAs($this->makeAdmin())
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Total units')
            ->assertSee('Stock by size')
            ->assertSee('40');
    }

    public function test_customer_widget_shows_recent_users(): void
    {
        $this->makeUser();

        $this->actingAs($this->makeAdmin())
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Customer overview')
            ->assertSee('Recently joined');
    }

    public function test_activity_widget_shows_inventory_history(): void
    {
        $product = $this->makeProductWithStock([], 5);
        InventoryHistory::create([
            'product_id' => $product->id,
            'previous_quantity' => 2,
            'new_quantity' => 5,
            'change_amount' => 3,
            'change_type' => 'restock',
        ]);

        $this->actingAs($this->makeAdmin())
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('restock')
            ->assertSee('3 unit(s)', false);
    }

    public function test_all_time_range_is_accepted(): void
    {
        $this->actingAs($this->makeAdmin())
            ->get(route('admin.dashboard', ['range' => 'all']))
            ->assertOk()
            ->assertSee('All time');
    }

    public function test_order_status_overview_links_to_filtered_list(): void
    {
        $this->makeOrder(['status' => 'pending']);

        $this->actingAs($this->makeAdmin())
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('status=pending', false);
    }
}