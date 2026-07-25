<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class CartController extends Controller
{
    /**
     * Show the cart page with full product details.
     */
    public function index()
    {
        $cart = Session::get('cart', []);

        $products = Product::with('category')
            ->whereIn('id', array_keys($cart))
            ->get()
            ->map(function ($product) use ($cart) {
                $product->cart_quantity = $cart[$product->id]['quantity'];
                $product->line_total = $product->price * $product->cart_quantity;
                return $product;
            });

        $total = $products->sum('line_total');

        return view('cart.index', [
            'products' => $products,
            'total' => $total,
        ]);
    }

    /**
     * Add a product to the cart (or increase its quantity).
     */
    public function add(Request $request, Product $product)
    {
        $quantity = max(1, (int) $request->input('quantity', 1));
        $size = $request->input('size');

        $cart = Session::get('cart', []);

        if (isset($cart[$product->id])) {
            $cart[$product->id]['quantity'] += $quantity;
            if ($size) $cart[$product->id]['size'] = $size;
        } else {
            $cart[$product->id] = ['quantity' => $quantity, 'size' => $size];
        }

        Session::put('cart', $cart);

        $msg = $size ? "{$product->name} (Size {$size}) added to cart" : "{$product->name} added to cart";

        return back()->with('cart_toast', [
            'message' => $msg,
            'count' => collect($cart)->sum('quantity'),
        ]);
    }

    /**
     * Update the quantity of a product already in the cart.
     */
    public function update(Request $request, Product $product)
    {
        $quantity = max(1, (int) $request->input('quantity', 1));

        $cart = Session::get('cart', []);

        if (isset($cart[$product->id])) {
            $cart[$product->id]['quantity'] = $quantity;
            Session::put('cart', $cart);
        }

        return back()->with('cart_toast', [
            'message' => 'Cart updated',
            'count' => collect($cart)->sum('quantity'),
        ]);
    }

    /**
     * Remove a product from the cart.
     */
    public function remove(Product $product)
    {
        $cart = Session::get('cart', []);

        unset($cart[$product->id]);

        Session::put('cart', $cart);

        return back()->with('cart_toast', [
            'message' => "{$product->name} removed from cart",
            'count' => collect($cart)->sum('quantity'),
        ]);
    }

    /**
     * Total number of items in the cart (for the navbar badge).
     */
    public static function itemCount(): int
    {
        return collect(Session::get('cart', []))->sum('quantity');
    }
}
