<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Payment;

class DashboardController extends Controller
{
    public function index()
    {
        $today = now()->startOfDay();
        
        $stats = [
            'total_products' => Product::count(),
            'active_products' => Product::where('is_active', true)->count(),
            'total_categories' => Category::count(),
            'total_brands' => Brand::count(),
            'total_carts' => Cart::count(),
            'low_stock_products' => Product::where('stock_quantity', '<', 10)->count(),
            'total_orders' => Order::count(),
            'today_orders' => Order::whereDate('created_at', $today)->count(),
            'total_sales' => Order::where('status', '!=', 'cancelled')->sum('total_amount'),
            'today_earnings' => Order::whereDate('created_at', $today)
                ->where('status', '!=', 'cancelled')
                ->sum('total_amount'),
        ];

        $recentProducts = Product::with(['category', 'brand'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $topSellingProducts = Order::selectRaw('order_items.product_id, products.name, SUM(order_items.quantity) as total_sold')
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->where('orders.status', '!=', 'cancelled')
            ->groupBy('order_items.product_id', 'products.name')
            ->orderBy('total_sold', 'desc')
            ->limit(5)
            ->get();

        // Sales data for chart (last 7 days)
        $salesData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->startOfDay();
            $salesData[] = [
                'date' => $date->format('M d'),
                'sales' => Order::whereDate('created_at', $date)
                    ->where('status', '!=', 'cancelled')
                    ->sum('total_amount'),
            ];
        }

        $recentOrders = Order::with(['user', 'items'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentProducts', 'topSellingProducts', 'salesData', 'recentOrders'));
    }
}

