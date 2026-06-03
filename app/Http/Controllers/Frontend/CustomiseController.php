<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Services\OmgsCatalogService;
use Illuminate\Http\Request;

class CustomiseController extends Controller
{
    /**
     * Hub page like omgs.in/customise/acrylic-print
     */
    public function show(string $categorySlug, Request $request)
    {
        $category = Category::where(function ($q) use ($categorySlug) {
            $q->where('slug', $categorySlug)->orWhere('hub_route_slug', $categorySlug);
        })
            ->where('is_active', true)
            ->with('parent')
            ->firstOrFail();

        $styleFilter = $request->get('style', 'all');

        $productsQuery = Product::with(['images', 'category'])
            ->where('category_id', $category->id)
            ->where('is_active', true)
            ->where('is_customizable', true)
            ->orderBy('sort_order');

        if ($styleFilter !== 'all') {
            $productsQuery->where('style_filter', $styleFilter);
        }

        $products = $productsQuery->get();

        $allProducts = Product::where('category_id', $category->id)
            ->where('is_active', true)
            ->where('is_customizable', true)
            ->whereNotNull('style_filter')
            ->pluck('style_filter')
            ->unique();

        $availableFilters = collect(['all' => 'All']);
        foreach (OmgsCatalogService::STYLE_FILTERS as $key => $label) {
            if ($key === 'all' || $allProducts->contains($key)) {
                $availableFilters[$key] = $label;
            }
        }

        $siblingCategories = Category::where('parent_id', $category->parent_id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $hubSlug = $category->hub_route_slug ?? $category->slug;

        return view('frontend.customise.hub', compact(
            'category',
            'products',
            'siblingCategories',
            'availableFilters',
            'styleFilter',
            'hubSlug'
        ));
    }

    public function index()
    {
        $parent = Category::where('slug', 'custom-photo-decor')
            ->where('is_active', true)
            ->first();

        $lines = $parent
            ? Category::where('parent_id', $parent->id)->where('is_active', true)->orderBy('sort_order')->get()
            : collect();

        $featuredCustom = Product::with('images', 'category')
            ->customizable()
            ->where('is_active', true)
            ->where('is_featured', true)
            ->limit(12)
            ->get();

        return view('frontend.customise.index', compact('parent', 'lines', 'featuredCustom'));
    }
}
