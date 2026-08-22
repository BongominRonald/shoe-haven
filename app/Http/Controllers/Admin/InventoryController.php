<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryHistory;
use App\Models\Product;
use App\Models\ProductStock;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::with('category', 'stock')->latest()->get();

        $lowStockThreshold = 5;
        $outOfStock = $products->filter(fn($p) => ($p->stock?->quantity ?? 0) === 0);
        $lowStock = $products->filter(function ($p) use ($lowStockThreshold) {
            $qty = $p->stock?->quantity ?? 0;
            return $qty > 0 && $qty <= $lowStockThreshold;
        });

        $filter = $request->query('filter');
        if (in_array($filter, ['low-stock', 'out-of-stock'], true)) {
            $products = $filter === 'low-stock' ? $lowStock->values() : $outOfStock->values();
        }

        $history = InventoryHistory::with('product')
            ->latest()
            ->take(100)
            ->get();

        return view('admin.inventory.index', compact(
            'products', 'history', 'outOfStock', 'lowStock', 'lowStockThreshold', 'filter'
        ));
    }

    public function updateStock(Request $request, Product $product)
    {
        $data = $request->validate(['quantity' => 'required|integer|min:0']);

        $stock = ProductStock::firstOrCreate(
            ['product_id' => $product->id],
            ['quantity' => 0]
        );

        $previous = $stock->quantity;
        $stock->update(['quantity' => $data['quantity']]);

        InventoryHistory::create([
            'product_id' => $product->id,
            'changed_by' => auth()->id(),
            'previous_quantity' => $previous,
            'new_quantity' => $data['quantity'],
            'change_amount' => $data['quantity'] - $previous,
            'change_type' => $data['quantity'] > $previous ? 'restock' : 'adjustment',
        ]);

        return redirect()->route('admin.inventory.index')
            ->with('status', "Stock for {$product->name} updated to {$data['quantity']}.");
    }
}
