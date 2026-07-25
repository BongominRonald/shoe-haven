<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;

class AdminController extends Controller
{
    public function dashboard()
    {
        $totalRevenue = Order::where('payment_status', 'paid')->sum('total_amount');
        $pendingOrders = Order::where('status', 'pending')->count();
        $recentOrders = Order::with('user')->latest()->take(5)->get();
        $ordersByStatus = [
            'pending' => Order::where('status', 'pending')->count(),
            'confirmed' => Order::where('status', 'confirmed')->count(),
            'shipped' => Order::where('status', 'shipped')->count(),
            'delivered' => Order::where('status', 'delivered')->count(),
            'cancelled' => Order::where('status', 'cancelled')->count(),
        ];

        $stats = [
            'total_products' => Product::count(),
            'total_categories' => Category::count(),
            'total_users' => User::count(),
            'total_orders' => Order::count(),
            'total_revenue' => $totalRevenue,
            'pending_orders' => $pendingOrders,
            'low_stock' => Product::whereHas('stock', fn($q) => $q->where('quantity', '<', 10))->count(),
            'recent_products' => Product::with('category')->latest()->take(5)->get(),
            'recent_orders' => $recentOrders,
            'orders_by_status' => $ordersByStatus,
        ];

        return view('admin.dashboard', $stats);
    }
}
