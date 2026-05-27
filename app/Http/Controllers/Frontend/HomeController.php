<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Banner;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $featuredProducts = Product::with(['category', 'brand', 'images'])
            ->where('is_active', true)
            ->where('is_featured', true)
            ->orderBy('sort_order')
            ->limit(8)
            ->get();

        $latestProducts = Product::with(['category', 'brand', 'images'])
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->limit(8)
            ->get();

        $categories = Category::where('is_active', true)
            ->whereNull('parent_id')
            ->with('children')
            ->orderBy('sort_order')
            ->limit(6)
            ->get();

        $banners = Banner::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return view('frontend.home', compact('featuredProducts', 'latestProducts', 'categories', 'banners'));
    }
}

