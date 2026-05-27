<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Attribute;
use Illuminate\Http\Request;

class ProductController extends Controller
{
		/**
		 * Return the given category id plus all descendant category ids.
		 */
		private function getCategoryAndDescendantIds(int $rootCategoryId): array
		{
			$ids = [$rootCategoryId];
			$current = [$rootCategoryId];

			while (!empty($current)) {
				$children = Category::whereIn('parent_id', $current)->pluck('id')->all();
				$children = array_values(array_diff($children, $ids));
				if (empty($children)) {
					break;
				}
				$ids = array_merge($ids, $children);
				$current = $children;
			}

			return $ids;
		}

    public function index(Request $request)
    {
        $query = Product::with(['category', 'brand', 'shop', 'images', 'variants.attributeValues'])
            ->where('is_active', true);

        // Category filter
        if ($request->has('category')) {
			$slug = $request->category;
			$category = Category::where('slug', $slug)->first();
			if ($category) {
				$ids = $this->getCategoryAndDescendantIds($category->id);
				$query->whereIn('category_id', $ids);
			} else {
				// no matching category, return empty set
				$query->whereRaw('1=0');
			}
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('tags', function($tagQuery) use ($search) {
                      $tagQuery->where('tag', 'like', "%{$search}%");
                  });
            });
        }

        // Price filter
        if ($request->has('min_price')) {
            $query->whereRaw('COALESCE(discount_price, price) >= ?', [$request->min_price]);
        }
        if ($request->has('max_price')) {
            $query->whereRaw('COALESCE(discount_price, price) <= ?', [$request->max_price]);
        }

        // Attribute filters
        foreach ($request->all() as $key => $value) {
            if (strpos($key, 'attr_') === 0) {
                $attributeId = str_replace('attr_', '', $key);
                $query->whereHas('variants.attributeValues', function($q) use ($attributeId, $value) {
                    $q->where('attribute_id', $attributeId)
                      ->whereIn('attribute_values.id', (array)$value);
                });
            }
        }

        // Sorting
        $sort = $request->get('sort', 'latest');
        switch ($sort) {
            case 'price_low':
                $query->orderByRaw('COALESCE(discount_price, price) ASC');
                break;
            case 'price_high':
                $query->orderByRaw('COALESCE(discount_price, price) DESC');
                break;
            case 'name':
                $query->orderBy('name', 'ASC');
                break;
            case 'latest':
            default:
                $query->orderBy('created_at', 'DESC');
                break;
        }

        $products = $query->paginate(24);
        $categories = Category::where('is_active', true)->get();
        
        // Get filterable attributes for active filters
        $filterableAttributes = Attribute::where('is_filterable', true)
            ->with('values')
            ->get();

        return view('frontend.products.index', compact('products', 'categories', 'filterableAttributes'));
    }

    public function show($slug)
    {
        $product = Product::with([
            'category.parent',
            'brand',
            'shop',
            'images.attributeValue',
            'tags',
            'variants.attributeValues.attribute',
        ])->where('slug', $slug)->where('is_active', true)->firstOrFail();

        $attributes = $product->category->applicableAttributes();

        $colorImages = [];
        foreach ($product->images as $image) {
            if ($image->attribute_value_id) {
                $colorImages[$image->attribute_value_id] = asset('storage/' . $image->image_path);
            }
        }

        foreach ($product->variants as $variant) {
            if (! $variant->image) {
                continue;
            }
            foreach ($variant->attributeValues as $value) {
                if ($value->attribute?->slug === 'color' && ! isset($colorImages[$value->id])) {
                    $colorImages[$value->id] = asset('storage/' . $variant->image);
                }
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
                'image' => $variant->image ? asset('storage/' . $variant->image) : null,
            ];
        });

        $relatedProducts = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('is_active', true)
            ->limit(4)
            ->get();

        return view('frontend.products.show', compact(
            'product',
            'relatedProducts',
            'attributes',
            'colorImages',
            'defaultImage',
            'variantsPayload'
        ));
    }

    public function getVariant(Request $request)
    {
        $product = Product::findOrFail($request->product_id);
        
        $selectedAttributes = $request->attributes ?? [];
        
        $variant = $product->variants()
            ->whereHas('attributeValues', function($q) use ($selectedAttributes) {
                foreach ($selectedAttributes as $attrId => $valueId) {
                    $q->where('attribute_values.id', $valueId);
                }
            }, '=', count($selectedAttributes))
            ->first();

        if ($variant) {
            return response()->json([
                'success' => true,
                'variant' => [
                    'id' => $variant->id,
                    'sku' => $variant->sku,
                    'price' => $variant->final_price,
                    'stock_quantity' => $variant->stock_quantity,
                    'in_stock' => $variant->in_stock,
                    'image' => $variant->image ? asset('storage/' . $variant->image) : null,
                ]
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Variant not found'
        ], 404);
    }
}

