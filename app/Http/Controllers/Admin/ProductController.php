<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductStock;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category', 'stock');

        if ($request->filled('search')) {
            $s = $request->string('search');
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('brand', 'like', "%{$s}%");
            });
        }

        $products = $query->latest()->paginate(30)->withQueryString();
        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::all();
        return view('admin.products.form', [
            'categories' => $categories,
            'product' => null,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'brand' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'original_price' => 'nullable|numeric|min:0',
            'image' => 'required|url|max:500',
            'category_id' => 'required|exists:categories,id',
            'is_new' => 'boolean',
            'discount' => 'nullable|integer|min:0|max:100',
            'stock' => 'required|integer|min:0',
        ]);

        $data['is_new'] = $request->boolean('is_new');

        $product = Product::create($data);

        ProductStock::create([
            'product_id' => $product->id,
            'quantity' => $data['stock'],
        ]);

        return redirect()->route('admin.products.index')
            ->with('status', 'Product created successfully.');
    }

    public function edit(Product $product)
    {
        $categories = Category::all();
        $product->load('stock');
        return view('admin.products.form', [
            'categories' => $categories,
            'product' => $product,
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'brand' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'original_price' => 'nullable|numeric|min:0',
            'image' => 'required|url|max:500',
            'category_id' => 'required|exists:categories,id',
            'is_new' => 'boolean',
            'discount' => 'nullable|integer|min:0|max:100',
            'stock' => 'required|integer|min:0',
        ]);

        $data['is_new'] = $request->boolean('is_new');
        $product->update($data);

        $product->stock()->updateOrCreate(
            ['product_id' => $product->id],
            ['quantity' => $data['stock']]
        );

        return redirect()->route('admin.products.index')
            ->with('status', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        $product->stock()->delete();
        $product->delete();

        return redirect()->route('admin.products.index')
            ->with('status', 'Product deleted successfully.');
    }
}
