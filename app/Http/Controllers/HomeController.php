<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\HeroSection;
use App\Models\Product;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::withCount('products')->get();

        $featuredProducts = Product::with(['category', 'sizeStock', 'images', 'comments'])
            ->latest('id')
            ->paginate(15)
            ->onEachSide(1);

        $saleProducts = Product::with(['category', 'sizeStock', 'images', 'comments'])
            ->where('discount', '>', 0)
            ->latest('discount')
            ->take(10)
            ->get();

        $heroes = HeroSection::getAllActive();

        // Recently viewed
        $recentIds = collect(explode(',', $request->cookie('recently_viewed', '')))
            ->filter()
            ->unique()
            ->take(8)
            ->toArray();
        $recentProducts = !empty($recentIds)
            ? Product::whereIn('id', $recentIds)->orderByRaw('FIELD(id,' . implode(',', $recentIds) . ')')->get()
            : collect();

        return view('welcome', [
            'categories' => $categories,
            'featuredProducts' => $featuredProducts,
            'heroes' => $heroes,
            'recentProducts' => $recentProducts,
            'saleProducts' => $saleProducts,
        ]);
    }
}
