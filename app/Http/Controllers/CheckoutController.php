<?php

namespace App\Http\Controllers;

use App\Models\InventoryHistory;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function index()
    {
        $cart = Session::get('cart', []);

        if (empty($cart)) {
            return redirect()->route('cart.index');
        }

        $products = Product::whereIn('id', array_keys($cart))->get();
        $total = $products->sum(fn($p) => $p->price * $cart[$p->id]['quantity']);

        return view('checkout.index', compact('products', 'cart', 'total'));
    }
    public function store(Request $request)
    {
        $cart = Session::get('cart', []);

        if (empty($cart)) {
            return redirect()->route('cart.index');
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:32',
            'address' => 'required|string',
            'payment_method' => 'required|in:mtn,airtel',
        ]);

        DB::beginTransaction();
        try {
            $productIds = array_keys($cart);
            $products = Product::whereIn('id', $productIds)->get();
            $total = $products->sum(fn($p) => $p->price * $cart[$p->id]['quantity']);

            $order = Order::create([
                'user_id' => Auth::id(),
                'total_amount' => $total,
                'payment_method' => $data['payment_method'] === 'mtn' ? 'MTN Mobile Money' : 'Airtel Money',
                'payment_phone' => $data['phone'],
                'status' => 'pending',
                'payment_status' => 'pending',
                'transaction_id' => 'TXN-' . strtoupper(Str::random(12)),
                'payment_initiated_at' => now(),
                'shipping_name' => $data['name'],
                'shipping_address' => $data['address'],
                'shipping_phone' => $data['phone'],
            ]);

            foreach ($products as $product) {
                $qty = $cart[$product->id]['quantity'];
                $size = $cart[$product->id]['size'] ?? null;

                if ($size) {
                    $sizeStock = $product->sizeStock()->where('size', $size)->first();
                    if (!$sizeStock || $sizeStock->quantity < $qty) {
                        throw new \Exception("Insufficient stock for {$product->name} (Size {$size}).");
                    }
                } else {
                    if (!$product->stock) {
                        throw new \Exception("No stock record for {$product->name}.");
                    }
                    $available = $product->stock->quantity ?? 0;
                    if ($available < $qty) {
                        throw new \Exception("Insufficient stock for {$product->name}.");
                    }
                }

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'price_at_sale' => $product->price,
                ]);

                $previous = $product->stock->quantity;

                if ($size) {
                    $sizeStock->decrement('quantity', $qty);
                }

                $product->stock()->decrement('quantity', $qty);

                InventoryHistory::create([
                    'product_id' => $product->id,
                    'previous_quantity' => $previous,
                    'new_quantity' => $previous - $qty,
                    'change_amount' => -$qty,
                    'change_type' => 'sale',
                    'notes' => 'Order #' . $order->id,
                    'changed_by' => Auth::id(),
                ]);
            }

            Session::forget('cart');
            DB::commit();

            return redirect()->route('orders.confirmation', $order);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function confirmation(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        $order->load('items.product');

        return view('orders.confirmation', compact('order'));
    }
}
