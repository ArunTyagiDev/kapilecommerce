<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\Category;
use App\Models\Product;
use App\Services\OmgsCatalogService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    private const PER_PAGE = 20;

    private function getCategoryAndDescendantIds(int $rootCategoryId): array
    {
        $ids = [$rootCategoryId];
        $current = [$rootCategoryId];

        while (! empty($current)) {
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

    private function resolveActiveCategory(Request $request): ?Category
    {
        if (! $request->filled('category')) {
            return null;
        }

        return Category::where('slug', $request->category)
            ->where('is_active', true)
            ->first();
    }

    private function buildProductQuery(Request $request): Builder
    {
        $query = Product::with(['category', 'brand', 'shop', 'images', 'variants.attributeValues'])
            ->where('is_active', true);

        $category = $this->resolveActiveCategory($request);
        if ($request->filled('category')) {
            if ($category) {
                $query->whereIn('category_id', $this->getCategoryAndDescendantIds($category->id));
            } else {
                $query->whereRaw('1=0');
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('tags', function ($tagQuery) use ($search) {
                        $tagQuery->where('tag', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('min_price')) {
            $query->whereRaw('COALESCE(discount_price, price) >= ?', [$request->min_price]);
        }
        if ($request->filled('max_price')) {
            $query->whereRaw('COALESCE(discount_price, price) <= ?', [$request->max_price]);
        }

        if ($request->filled('style') && $request->style !== 'all') {
            $query->where('style_filter', $request->style);
        }

        foreach ($request->all() as $key => $value) {
            if (str_starts_with($key, 'attr_') && $value !== '' && $value !== null) {
                $attributeId = str_replace('attr_', '', $key);
                $query->whereHas('variants.attributeValues', function ($q) use ($attributeId, $value) {
                    $q->where('attribute_id', $attributeId)
                        ->where('attribute_values.id', $value);
                });
            }
        }

        switch ($request->get('sort', 'latest')) {
            case 'price_low':
                $query->orderByRaw('COALESCE(discount_price, price) ASC');
                break;
            case 'price_high':
                $query->orderByRaw('COALESCE(discount_price, price) DESC');
                break;
            case 'name':
                $query->orderBy('name', 'ASC');
                break;
            default:
                $query->orderBy('created_at', 'DESC');
                break;
        }

        return $query;
    }

    /**
     * @return array{styleFilters: array<string, string>, categoryAttributes: \Illuminate\Support\Collection, activeStyle: string}
     */
    private function getFilterContext(?Category $activeCategory, Request $request): array
    {
        $styleFilters = [];
        $categoryAttributes = collect();
        $activeStyle = $request->get('style', 'all');

        if ($activeCategory) {
            $categoryAttributes = $activeCategory->applicableAttributes()
                ->filter(fn ($attr) => $attr->is_filterable)
                ->values();

            $usedStyles = Product::whereIn('category_id', $this->getCategoryAndDescendantIds($activeCategory->id))
                ->where('is_active', true)
                ->whereNotNull('style_filter')
                ->distinct()
                ->pluck('style_filter');

            if ($usedStyles->isNotEmpty()) {
                $styleFilters['all'] = 'All';
                foreach (OmgsCatalogService::STYLE_FILTERS as $key => $label) {
                    if ($key !== 'all' && $usedStyles->contains($key)) {
                        $styleFilters[$key] = $label;
                    }
                }
            }
        } else {
            $categoryAttributes = Attribute::where('is_filterable', true)
                ->with('values')
                ->get();
        }

        return compact('styleFilters', 'categoryAttributes', 'activeStyle');
    }

    public function index(Request $request)
    {
        $activeCategory = $this->resolveActiveCategory($request);
        $query = $this->buildProductQuery($request);
        $total = (clone $query)->count();
        $products = $query->take(self::PER_PAGE)->get();

        $rootCategories = Category::whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $childCategories = collect();
        if ($activeCategory) {
            if ($activeCategory->parent_id) {
                $childCategories = Category::where('parent_id', $activeCategory->parent_id)
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->get();
            } else {
                $childCategories = Category::where('parent_id', $activeCategory->id)
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->get();
            }
        }

        $filterContext = $this->getFilterContext($activeCategory, $request);

        return view('frontend.products.index', array_merge(
            compact('products', 'activeCategory', 'rootCategories', 'childCategories', 'total'),
            $filterContext,
            [
                'hasMore' => $total > $products->count(),
                'minPrice' => $request->min_price,
                'maxPrice' => $request->max_price,
            ]
        ));
    }

    public function load(Request $request)
    {
        $page = max(1, (int) $request->get('page', 1));
        $query = $this->buildProductQuery($request);
        $total = (clone $query)->count();
        $products = $query->skip(($page - 1) * self::PER_PAGE)
            ->take(self::PER_PAGE)
            ->get();

        $html = view('frontend.products.partials.grid-items', compact('products'))->render();
        $loaded = min($page * self::PER_PAGE, $total);

        return response()->json([
            'html' => $html,
            'has_more' => $loaded < $total,
            'total' => $total,
            'loaded' => $loaded,
        ]);
    }

    public function filters(Request $request)
    {
        $activeCategory = $this->resolveActiveCategory($request);
        $filterContext = $this->getFilterContext($activeCategory, $request);

        $html = view('frontend.products.partials.filters', array_merge($filterContext, [
            'minPrice' => $request->min_price,
            'maxPrice' => $request->max_price,
        ]))->render();

        return response()->json(['html' => $html]);
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

        if ($product->is_customizable) {
            return redirect()->route('custom.product', $product->slug);
        }

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
            ->whereHas('attributeValues', function ($q) use ($selectedAttributes) {
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
                ],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Variant not found',
        ], 404);
    }
}
