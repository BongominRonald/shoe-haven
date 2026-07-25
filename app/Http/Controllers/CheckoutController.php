<?php

namespace App\Http\Controllers;

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
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $cart[$product->id]['quantity'],
                    'price_at_sale' => $product->price,
                ]);
            }

            Session::forget('cart');
            DB::commit();

            return redirect()->route('orders.confirmation', $order);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Something went wrong. Please try again.']);
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
