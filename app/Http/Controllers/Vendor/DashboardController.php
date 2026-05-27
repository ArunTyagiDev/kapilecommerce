<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $shop = Auth::user()->shop;

        $stats = [
            'products' => Product::where('shop_id', $shop->id)->count(),
            'active_products' => Product::where('shop_id', $shop->id)->where('is_active', true)->count(),
            'orders' => OrderItem::where('shop_id', $shop->id)->distinct('order_id')->count('order_id'),
            'revenue' => OrderItem::where('shop_id', $shop->id)->sum('subtotal'),
        ];

        $recentProducts = Product::where('shop_id', $shop->id)
            ->with('category')
            ->latest()
            ->limit(5)
            ->get();

        return view('vendor.dashboard', compact('shop', 'stats', 'recentProducts'));
    }
}
