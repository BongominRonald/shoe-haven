<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\ContactMessage;
use App\Models\InventoryHistory;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;

class DashboardController extends Controller
{
    private const LOW_STOCK_THRESHOLD = 5;

    private const STATUS_BADGES = [
        'pending' => 'warning',
        'confirmed' => 'info',
        'shipped' => 'primary',
        'delivered' => 'success',
        'cancelled' => 'danger',
    ];

    private const PAYMENT_BADGES = [
        'pending' => 'warning',
        'paid' => 'success',
        'failed' => 'danger',
    ];

    private const WIDGETS = ['kpis', 'chart', 'sales', 'orders', 'attention', 'recent_orders', 'best_sellers', 'inventory', 'customers', 'activity'];

    public function __invoke(Request $request)
    {
        $range = $this->resolveRange($request);
        $capabilities = $this->capabilities($request->user());

        $data = [
            'range' => $range,
            'capabilities' => $capabilities,
            'attention_items' => $this->attentionData($request->user()),
        ];

        foreach (self::WIDGETS as $widget) {
            if (!($capabilities[$widget] ?? false)) {
                continue;
            }
            $data[$widget] = $this->safe($widget, fn () => match ($widget) {
                'kpis' => $this->kpiData($range),
                'chart' => $this->chartData($range),
                'sales' => $this->salesData($range),
                'orders' => $this->ordersOverview($range),
                'recent_orders' => $this->recentOrders(),
                'best_sellers' => $this->bestSellers($range),
                'inventory' => $this->inventoryData(),
                'customers' => $this->customerData($range),
                'activity' => $this->activityData(),
                default => null,
            });
        }

        return View::make('admin.dashboard', $data);
    }

    private function safe(string $widget, callable $cb): mixed
    {
        try {
            return $cb();
        } catch (\Throwable $e) {
            Log::error("Dashboard widget [{$widget}] failed", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return ['error' => 'This widget could not be loaded right now.'];
        }
    }

    private function capabilities($user): array
    {
        $roles = config('admin-nav.roles', []);

        $cap = [];
        foreach (self::WIDGETS as $widget) {
            $cap[$widget] = true;
            if ($roles && isset($roles[$widget])) {
                $allowed = (array) $roles[$widget];
                $cap[$widget] = $user->roleNames()
                    ? array_intersect($user->roleNames(), $allowed) !== []
                    : in_array('admin', $allowed);
            }
        }
        return $cap;
    }

    private function resolveRange(Request $request): array
    {
        $presets = [
            'today' => [fn () => Carbon::today(), fn () => Carbon::tomorrow()],
            'yesterday' => [fn () => Carbon::yesterday(), fn () => Carbon::today()],
            '7d' => [fn () => Carbon::today()->subDays(6), fn () => Carbon::tomorrow()],
            '30d' => [fn () => Carbon::today()->subDays(29), fn () => Carbon::tomorrow()],
            'this-month' => [fn () => Carbon::today()->startOfMonth(), fn () => Carbon::today()->startOfMonth()->addMonth()],
            'last-month' => [fn () => Carbon::today()->startOfMonth()->subMonth(), fn () => Carbon::today()->startOfMonth()],
            'this-year' => [fn () => Carbon::today()->startOfYear(), fn () => Carbon::today()->startOfYear()->addYear()],
            'all' => [fn () => Carbon::createFromFormat('Y-m-d', '2024-01-01'), fn () => Carbon::tomorrow()],
        ];

        $key = $request->query('range', '30d');
        $from = null;
        $to = null;

        if ($key === 'custom') {
            $from = $request->query('from') ? Carbon::parse($request->query('from'))->startOfDay() : Carbon::today()->subDays(29)->startOfDay();
            $to = $request->query('to') ? Carbon::parse($request->query('to'))->endOfDay() : Carbon::today()->endOfDay();
            if ($from->gt($to)) {
                [$from, $to] = [$to, $from];
            }
            $key = 'custom';
        } elseif (isset($presets[$key])) {
            [$from, $to] = $presets[$key];
            $from = $from()->startOfDay();
            $to = $to()->startOfDay();
        } else {
            $key = '30d';
            [$from, $to] = $presets['30d'];
            $from = $from()->startOfDay();
            $to = $to()->startOfDay();
        }

        $spanDays = max(1, (int) $to->copy()->subDay()->diffInDays($from) + 1);
        $granularity = $spanDays <= 1 ? 'hour' : ($spanDays <= 92 ? 'day' : 'month');
        $spanLabel = match ($key) {
            'today' => 'Today',
            'yesterday' => 'Yesterday',
            '7d' => 'Last 7 days',
            '30d' => 'Last 30 days',
            'this-month' => 'This month',
            'last-month' => 'Last month',
            'this-year' => 'This year',
            'all' => 'All time',
            default => 'Custom range',
        };

        $prevFrom = $from->copy()->subDays($spanDays);
        $prevTo = $from->copy();

        return compact('from', 'to', 'prevFrom', 'prevTo', 'granularity', 'spanDays', 'spanLabel', 'key');
    }

    private function kpiData(array $range): array
    {
        $cacheKey = 'dashboard.kpis.' . $range['from']->toDateString() . '.' . $range['to']->toDateString();

        return Cache::remember($cacheKey, 60, function () use ($range) {
            $cur = $this->orderAggregates($range['from'], $range['to']);
            $prev = $this->orderAggregates($range['prevFrom'], $range['prevTo']);

            $newCustomers = User::whereBetween('created_at', [$range['from'], $range['to']])->count();
            $prevCustomers = User::whereBetween('created_at', [$range['prevFrom'], $range['prevTo']])->count();

            $products = Product::count();
            $pendingOrders = Order::where('status', 'pending')->count();
            $lowStock = Product::whereHas('stock', fn ($q) => $q->where('quantity', '<', self::LOW_STOCK_THRESHOLD))->count();
            $outOfStock = Product::whereDoesntHave('stock', fn ($q) => $q->where('quantity', '>', 0))->count();

            return [
                'revenue' => $cur['revenue'],
                'revenue_prev' => $prev['revenue'],
                'orders' => $cur['orders'],
                'orders_prev' => $prev['orders'],
                'customers' => $newCustomers,
                'customers_prev' => $prevCustomers,
                'products' => $products,
                'pending_orders' => $pendingOrders,
                'low_stock' => $lowStock,
                'out_of_stock' => $outOfStock,
            ];
        });
    }

    private function orderAggregates(Carbon $from, Carbon $to): array
    {
        $row = Order::whereBetween('created_at', [$from, $to])
            ->where('status', '!=', 'cancelled')
            ->selectRaw('COALESCE(SUM(total_amount), 0) as revenue, COUNT(*) as orders')
            ->first();

        return [
            'revenue' => (float) ($row->revenue ?? 0),
            'orders' => (int) ($row->orders ?? 0),
        ];
    }

    private function chartData(array $range): array
    {
        $cacheKey = 'dashboard.chart.' . $range['from']->toDateString() . '.' . $range['to']->toDateString() . '.' . $range['granularity'];

        return Cache::remember($cacheKey, 60, function () use ($range) {
            $labels = [];
            $revenue = [];
            $orders = [];

            if ($range['granularity'] === 'hour') {
                for ($h = 0; $h < 24; $h++) {
                    $labels[] = Carbon::today()->startOfDay()->addHours($h)->format('g A');
                    $revenue[$h] = 0;
                    $orders[$h] = 0;
                }
                $rows = Order::whereBetween('created_at', [$range['from'], $range['to']])
                    ->where('status', '!=', 'cancelled')
                    ->selectRaw("HOUR(created_at) as bucket, COALESCE(SUM(total_amount), 0) as rev, COUNT(*) as cnt")
                    ->groupBy('bucket')
                    ->get();
                foreach ($rows as $row) {
                    $revenue[(int) $row->bucket] = (float) $row->rev;
                    $orders[(int) $row->bucket] = (int) $row->cnt;
                }
            } elseif ($range['granularity'] === 'day') {
                for ($d = $range['from']->copy(); $d->lt($range['to']); $d->addDay()) {
                    $labels[] = $d->format('M j');
                    $revenue[$d->toDateString()] = 0;
                    $orders[$d->toDateString()] = 0;
                }
                $rows = Order::whereBetween('created_at', [$range['from'], $range['to']])
                    ->where('status', '!=', 'cancelled')
                    ->selectRaw("DATE(created_at) as bucket, COALESCE(SUM(total_amount), 0) as rev, COUNT(*) as cnt")
                    ->groupBy('bucket')
                    ->get();
                foreach ($rows as $row) {
                    $key = Carbon::parse($row->bucket)->toDateString();
                    if (array_key_exists($key, $revenue)) {
                        $revenue[$key] = (float) $row->rev;
                        $orders[$key] = (int) $row->cnt;
                    }
                }
            } else {
                for ($m = $range['from']->copy()->startOfMonth(); $m->lt($range['to']); $m->addMonth()) {
                    $labels[] = $m->format('M Y');
                    $revenue[$m->format('Y-m')] = 0;
                    $orders[$m->format('Y-m')] = 0;
                }
                $rows = Order::whereBetween('created_at', [$range['from'], $range['to']])
                    ->where('status', '!=', 'cancelled')
                    ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as bucket, COALESCE(SUM(total_amount), 0) as rev, COUNT(*) as cnt")
                    ->groupBy('bucket')
                    ->get();
                foreach ($rows as $row) {
                    if (array_key_exists($row->bucket, $revenue)) {
                        $revenue[$row->bucket] = (float) $row->rev;
                        $orders[$row->bucket] = (int) $row->cnt;
                    }
                }
            }

            return [
                'labels' => array_values($labels),
                'revenue' => array_values($revenue),
                'orders' => array_values($orders),
            ];
        });
    }

    private function salesData(array $range): array
    {
        $cur = $this->orderAggregates($range['from'], $range['to']);
        $prev = $this->orderAggregates($range['prevFrom'], $range['prevTo']);

        $delivered = Order::whereBetween('created_at', [$range['from'], $range['to']])->where('status', 'delivered')->count();
        $cancelled = Order::whereBetween('created_at', [$range['from'], $range['to']])->where('status', 'cancelled')->count();
        $prevDelivered = Order::whereBetween('created_at', [$range['prevFrom'], $range['prevTo']])->where('status', 'delivered')->count();
        $prevCancelled = Order::whereBetween('created_at', [$range['prevFrom'], $range['prevTo']])->where('status', 'cancelled')->count();

        $aov = $cur['orders'] > 0 ? $cur['revenue'] / $cur['orders'] : 0;
        $prevAov = $prev['orders'] > 0 ? $prev['revenue'] / $prev['orders'] : 0;

        return [
            'revenue' => $cur['revenue'],
            'revenue_pct' => $this->percentChange($cur['revenue'], $prev['revenue']),
            'revenue_abs' => $cur['revenue'] - $prev['revenue'],
            'has_prev' => $prev['revenue'] > 0 || $prev['orders'] > 0,
            'orders' => $cur['orders'],
            'orders_pct' => $this->percentChange($cur['orders'], $prev['orders']),
            'orders_abs' => $cur['orders'] - $prev['orders'],
            'aov' => $aov,
            'aov_pct' => $this->percentChange($aov, $prevAov),
            'aov_abs' => $aov - $prevAov,
            'delivered' => $delivered,
            'delivered_pct' => $this->percentChange($delivered, $prevDelivered),
            'cancelled' => $cancelled,
            'cancelled_pct' => $this->percentChange($cancelled, $prevCancelled),
        ];
    }

    private function percentChange(float $current, float $previous): ?float
    {
        if ($previous == 0) {
            return $current == 0 ? 0 : null;
        }
        return round((($current - $previous) / $previous) * 100, 1);
    }

    private function ordersOverview(array $range): array
    {
        $rows = Order::whereBetween('created_at', [$range['from'], $range['to']])
            ->selectRaw('status, COUNT(*) as cnt')
            ->groupBy('status')
            ->pluck('cnt', 'status');

        $statuses = ['pending', 'confirmed', 'shipped', 'delivered', 'cancelled'];
        $total = 0;
        $items = [];
        foreach ($statuses as $status) {
            $count = (int) ($rows[$status] ?? 0);
            $total += $count;
            $items[] = [
                'status' => $status,
                'label' => ucfirst($status),
                'count' => $count,
                'badge' => self::STATUS_BADGES[$status] ?? 'secondary',
                'url' => route('admin.orders.index', ['status' => $status]),
            ];
        }

        return ['items' => $items, 'total' => $total];
    }

    private function attentionData($user): array
    {
        $items = [];

        $outOfStock = Product::whereDoesntHave('stock', fn ($q) => $q->where('quantity', '>', 0))->count();
        if ($outOfStock > 0) {
            $items[] = [
                'priority' => 'high',
                'icon' => 'bi-box-seam',
                'color' => 'danger',
                'title' => "{$outOfStock} product(s) out of stock",
                'desc' => 'No quantity available — restock or disable them.',
                'url' => route('admin.inventory.index', ['filter' => 'out-of-stock']),
            ];
        }

        $failedPayments = Order::where('payment_status', 'failed')->count();
        if ($failedPayments > 0) {
            $items[] = [
                'priority' => 'high',
                'icon' => 'bi-credit-card-2-front',
                'color' => 'danger',
                'title' => "{$failedPayments} failed payment(s)",
                'desc' => 'Follow up with customers about payment issues.',
                'url' => route('admin.orders.index', ['payment_status' => 'failed']),
            ];
        }

        $lowStock = Product::whereHas('stock', fn ($q) => $q->where('quantity', '<', self::LOW_STOCK_THRESHOLD))->count();
        if ($lowStock > 0) {
            $items[] = [
                'priority' => 'medium',
                'icon' => 'bi-exclamation-diamond',
                'color' => 'warning',
                'title' => "{$lowStock} product(s) running low",
                'desc' => 'Below ' . self::LOW_STOCK_THRESHOLD . ' units — reorder soon.',
                'url' => route('admin.inventory.index', ['filter' => 'low-stock']),
            ];
        }

        $pendingOrders = Order::where('status', 'pending')->count();
        if ($pendingOrders > 0) {
            $items[] = [
                'priority' => 'medium',
                'icon' => 'bi-hourglass-split',
                'color' => 'warning',
                'title' => "{$pendingOrders} pending order(s)",
                'desc' => 'Awaiting confirmation.',
                'url' => route('admin.orders.index', ['status' => 'pending']),
            ];
        }

        $pendingReviews = Comment::where('status', 'pending')->count();
        if ($pendingReviews > 0) {
            $items[] = [
                'priority' => 'medium',
                'icon' => 'bi-chat-left-quote',
                'color' => 'info',
                'title' => "{$pendingReviews} review(s) awaiting moderation",
                'desc' => 'Review comments before they go live.',
                'url' => null,
            ];
        }

        $unreadMessages = ContactMessage::where('is_read', false)->count();
        if ($unreadMessages > 0) {
            $items[] = [
                'priority' => 'low',
                'icon' => 'bi-envelope-open',
                'color' => 'secondary',
                'title' => "{$unreadMessages} unread message(s)",
                'desc' => 'Customer enquiries need a reply.',
                'url' => route('admin.messages.index'),
            ];
        }

        return $items;
    }

    private function recentOrders(): array
    {
        return Order::with('user:id,name')
            ->latest('created_at')
            ->limit(6)
            ->get()
            ->map(fn (Order $order) => [
                'id' => $order->id,
                'number' => '#' . str_pad((string) $order->id, 4, '0', STR_PAD_LEFT),
                'customer' => $order->user?->name ?? 'Guest',
                'total' => (float) $order->total_amount,
                'status' => $order->status,
                'badge' => self::STATUS_BADGES[$order->status] ?? 'secondary',
                'created_at' => $order->created_at?->diffForHumans(),
                'url' => route('admin.orders.show', $order->id),
            ])
            ->all();
    }

    private function bestSellers(array $range): array
    {
        $cacheKey = 'dashboard.bestsellers.' . $range['from']->toDateString() . '.' . $range['to']->toDateString();

        return Cache::remember($cacheKey, 60, function () use ($range) {
            return OrderItem::query()
                ->join('orders', 'orders.id', '=', 'order_items.order_id')
                ->where('orders.status', '!=', 'cancelled')
                ->whereBetween('orders.created_at', [$range['from'], $range['to']])
                ->selectRaw('order_items.product_id, SUM(order_items.quantity) as sold, SUM(order_items.quantity * order_items.price_at_sale) as revenue')
                ->groupBy('order_items.product_id')
                ->orderByDesc('sold')
                ->limit(5)
                ->get()
                ->map(function ($item) {
                    $product = Product::find($item->product_id);
                    return [
                        'product' => $product?->name ?? 'Deleted product #' . $item->product_id,
                        'image' => $product?->image ?? null,
                        'sold' => (int) $item->sold,
                        'revenue' => (float) $item->revenue,
                        'url' => $product ? route('admin.products.edit', $product->id) : null,
                    ];
                })
                ->all();
        });
    }

    private function inventoryData(): array
    {
        $totals = Product::query()
            ->leftJoin('product_stock', 'product_stock.product_id', '=', 'products.id')
            ->selectRaw('COUNT(DISTINCT products.id) as products, COALESCE(SUM(product_stock.quantity), 0) as total_units')
            ->first();

        $sizeStock = Cache::remember('dashboard.sizestock', 300, function () {
            return DB::table('product_size_stock as pss')
                ->selectRaw('pss.size, COALESCE(SUM(pss.quantity), 0) as qty')
                ->groupBy('pss.size')
                ->orderByDesc('qty')
                ->limit(8)
                ->get()
                ->map(fn ($row) => ['size' => $row->size, 'qty' => (int) $row->qty])
                ->all();
        });

        return [
            'product_count' => (int) ($totals->products ?? 0),
            'total_units' => (int) ($totals->total_units ?? 0),
            'low_stock' => Product::whereHas('stock', fn ($q) => $q->where('quantity', '<', self::LOW_STOCK_THRESHOLD))->count(),
            'out_of_stock' => Product::whereDoesntHave('stock', fn ($q) => $q->where('quantity', '>', 0))->count(),
            'size_stock' => $sizeStock,
        ];
    }

    private function customerData(array $range): array
    {
        $total = User::count();
        $new = User::whereBetween('created_at', [$range['from'], $range['to']])->count();
        $returning = Order::whereBetween('created_at', [$range['from'], $range['to']])
            ->where('status', '!=', 'cancelled')
            ->whereIn('user_id', function ($q) use ($range) {
                $q->select('user_id')
                    ->from('orders')
                    ->where('user_id', '!=', null)
                    ->whereBetween('created_at', [$range['prevFrom'], $range['prevTo']])
                    ->where('status', '!=', 'cancelled')
                    ->groupBy('user_id');
            })
            ->distinct('user_id')
            ->count('user_id');

        $recent = User::latest('created_at')->limit(5)->get(['id', 'name', 'email', 'created_at']);

        return [
            'total' => $total,
            'new' => $new,
            'returning' => $returning,
            'recent' => $recent->map(fn (User $u) => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'joined' => $u->created_at?->diffForHumans(),
                'url' => route('admin.users.show', $u->id),
            ])->all(),
        ];
    }

    private function activityData(): array
    {
        return InventoryHistory::query()
            ->with('changedBy:id,name')
            ->latest('created_at')
            ->limit(8)
            ->get()
            ->map(fn (InventoryHistory $h) => [
                'id' => $h->id,
                'user' => $h->changedBy?->name ?? 'System',
                'action' => $h->change_type ?? 'updated',
                'qty' => (int) ($h->change_amount ?? 0),
                'note' => $h->notes ?? null,
                'created_at' => $h->created_at?->diffForHumans(),
            ])
            ->all();
    }
}