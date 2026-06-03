<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;

class CustomProductController extends Controller
{
    /**
     * Custom product editor like omgs.in/custom/1-portrait-acrylic-wall-photo
     */
    public function show(string $slug)
    {
        $product = Product::with([
            'category.parent',
            'brand',
            'shop',
            'images.attributeValue',
            'variants.attributeValues.attribute',
        ])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->where('is_customizable', true)
            ->firstOrFail();

        $attributes = $product->category->applicableAttributes();

        $colorImages = [];
        foreach ($product->images as $image) {
            if ($image->attribute_value_id) {
                $colorImages[$image->attribute_value_id] = asset('storage/' . $image->image_path);
            }
        }

        $defaultImage = $product->images->firstWhere('is_primary', true)
            ?? $product->images->firstWhere('attribute_value_id', null)
            ?? $product->images->first();

        $variantsPayload = $product->variants->map(function ($variant) {
            $attrs = [];
            foreach ($variant->attributeValues as $value) {
                $attrs[$value->attribute_id] = $value->id;
            }

            return [
                'id' => $variant->id,
                'attributes' => $attrs,
                'price' => (float) $variant->final_price,
                'stock_quantity' => $variant->stock_quantity,
                'in_stock' => $variant->in_stock,
            ];
        });

        $relatedStyles = Product::with('primaryImage')
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('is_customizable', true)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->limit(12)
            ->get();

        return view('frontend.customise.product', compact(
            'product',
            'attributes',
            'colorImages',
            'defaultImage',
            'variantsPayload',
            'relatedStyles'
        ));
    }
}
