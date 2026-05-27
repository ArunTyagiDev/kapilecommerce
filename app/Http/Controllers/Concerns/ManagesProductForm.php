<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Attribute;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

trait ManagesProductForm
{
    protected function productFormCategories(): Collection
    {
        return Category::where('is_active', true)
            ->whereNotNull('parent_id')
            ->with('parent')
            ->orderBy('parent_id')
            ->orderBy('sort_order')
            ->get();
    }

    protected function colorAttributeValues(Product $product): Collection
    {
        $product->loadMissing('category.parent');
        $colorAttr = $product->category->applicableAttributes()->firstWhere('slug', 'color');

        return $colorAttr ? $colorAttr->values : collect();
    }

    protected function saveGalleryImages(Product $product, Request $request, bool $isNew = false): void
    {
        if (! $request->hasFile('images')) {
            return;
        }

        $startOrder = (int) $product->images()->max('sort_order');
        $hasPrimary = $product->images()->where('is_primary', true)->exists();

        foreach ($request->file('images') as $index => $image) {
            $path = $image->store('products', 'public');
            $product->images()->create([
                'image_path' => $path,
                'is_primary' => ($isNew && $index === 0) || (! $hasPrimary && $index === 0),
                'sort_order' => $startOrder + $index + 1,
            ]);
            if ($index === 0 && ! $hasPrimary) {
                $hasPrimary = true;
            }
        }
    }

    protected function saveColorImages(Product $product, Request $request): void
    {
        if (! $request->hasFile('color_images')) {
            return;
        }

        foreach ($request->file('color_images') as $valueId => $image) {
            if (! $image) {
                continue;
            }

            $path = $image->store('products', 'public');
            $product->images()->updateOrCreate(
                ['product_id' => $product->id, 'attribute_value_id' => $valueId],
                [
                    'image_path' => $path,
                    'is_primary' => ! $product->images()->where('is_primary', true)->exists(),
                    'sort_order' => (int) $product->images()->max('sort_order') + 1,
                ]
            );
        }
    }

    protected function updateVariantsFromRequest(Product $product, Request $request): void
    {
        if (! $request->has('variants')) {
            return;
        }

        foreach ($request->input('variants', []) as $variantId => $data) {
            $variant = ProductVariant::where('product_id', $product->id)->find($variantId);
            if (! $variant) {
                continue;
            }

            $variant->update([
                'stock_quantity' => $data['stock_quantity'] ?? $variant->stock_quantity,
                'in_stock' => ($data['stock_quantity'] ?? $variant->stock_quantity) > 0,
            ]);

            if ($request->hasFile("variants.{$variantId}.image")) {
                $path = $request->file("variants.{$variantId}.image")->store('products', 'public');
                $variant->update(['image' => $path]);
            }
        }
    }

    protected function setPrimaryImage(Product $product, int $imageId): void
    {
        $product->images()->update(['is_primary' => false]);
        $product->images()->where('id', $imageId)->update(['is_primary' => true]);
    }
}
