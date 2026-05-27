<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Attribute;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;

trait ManagesProductVariants
{
    public function getCategoryAttributes($categoryId)
    {
        $category = Category::with('parent')->findOrFail($categoryId);
        $attributes = $category->applicableAttributes();

        if ($attributes->isEmpty()) {
            return response()->json([
                'attributes' => [],
                'message' => 'No attributes linked to this category. In Admin go to Categories → open the subcategory (e.g. Women Shoes) → Attributes → attach Size and Color.',
            ]);
        }

        return response()->json(['attributes' => $attributes]);
    }

    protected function generateVariants(Product $product, Request $request): void
    {
        $category = $product->category;
        $attributes = $category->attributes()->where('type', 'select')->get();

        if ($attributes->isEmpty()) {
            return;
        }

        $selectedAttributes = [];
        foreach ($attributes as $attribute) {
            $key = 'attribute_' . $attribute->id;
            if ($request->has($key) && is_array($request->$key)) {
                $selectedAttributes[$attribute->id] = $request->$key;
            }
        }

        if (empty($selectedAttributes)) {
            return;
        }

        $combinations = $this->generateCombinations($selectedAttributes);

        foreach ($combinations as $combination) {
            $variant = ProductVariant::create([
                'product_id' => $product->id,
                'stock_quantity' => $request->variant_stock_quantity ?? random_int(5, 50),
                'in_stock' => true,
            ]);

            foreach ($combination as $attributeId => $valueId) {
                $variant->attributes()->attach($attributeId, [
                    'attribute_value_id' => $valueId,
                ]);
            }
        }
    }

    protected function generateCombinations(array $arrays): array
    {
        $result = [[]];

        foreach ($arrays as $key => $values) {
            $newResult = [];
            foreach ($result as $product) {
                foreach ($values as $value) {
                    $newResult[] = array_merge($product, [$key => $value]);
                }
            }
            $result = $newResult;
        }

        return $result;
    }

    public function generateVariantsAction(Request $request, Product $product)
    {
        $this->generateVariants($product, $request);

        return redirect()->back()->with('success', 'Variants generated successfully.');
    }
}
