<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ManagesProductForm;
use App\Http\Controllers\Concerns\ManagesProductVariants;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    use ManagesProductVariants, ManagesProductForm;

    private function shopId(): int
    {
        return Auth::user()->shop->id;
    }

    private function authorizeProduct(Product $product): void
    {
        abort_unless($product->shop_id === $this->shopId(), 403);
    }

    public function index(Request $request)
    {
        $query = Product::with(['category', 'brand', 'images', 'variants'])
            ->where('shop_id', $this->shopId());

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $products = $query->orderByDesc('created_at')->paginate(20);
        $categories = Category::where('is_active', true)->get();
        $brands = Brand::where('is_active', true)->get();

        return view('vendor.products.index', compact('products', 'categories', 'brands'));
    }

    public function create()
    {
        $categories = $this->productFormCategories();
        $brands = Brand::where('is_active', true)->get();
        $attributesUrl = url('/vendor/products/category/__ID__/attributes');

        return view('vendor.products.create', compact('categories', 'brands', 'attributesUrl'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:products',
            'description' => 'nullable|string',
            'short_description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'sku' => 'nullable|string|max:255|unique:products',
            'barcode' => 'nullable|string|max:255',
            'price' => 'required|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0',
            'stock_quantity' => 'nullable|integer|min:0',
            'manage_stock' => 'boolean',
            'in_stock' => 'boolean',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'tags' => 'nullable|string',
            'images' => 'nullable|array',
            'images.*' => 'image|max:2048',
        ]);

        $validated['shop_id'] = $this->shopId();

        DB::beginTransaction();
        try {
            if (empty($validated['slug'])) {
                $validated['slug'] = Str::slug($validated['name']);
            }

            $product = Product::create($validated);

            $this->saveGalleryImages($product, $request, true);
            $this->saveColorImages($product, $request);

            if ($request->has('tags')) {
                $tags = array_filter(array_map('trim', explode(',', $request->tags)));
                foreach ($tags as $tag) {
                    $product->tags()->create(['tag' => $tag]);
                }
            }

            if ($request->has('generate_variants') && $request->generate_variants) {
                $this->generateVariants($product, $request);
            }

            DB::commit();

            return redirect()->route('vendor.products.index')
                ->with('success', 'Product created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->withInput()
                ->with('error', 'Error creating product: ' . $e->getMessage());
        }
    }

    public function edit(Product $product)
    {
        $this->authorizeProduct($product);
        $product->load(['category.attributes.values', 'brand', 'images', 'tags', 'variants.attributeValues']);
        $product->load(['category.parent', 'images.attributeValue', 'variants.attributeValues.attribute']);
        $categories = $this->productFormCategories();
        $brands = Brand::where('is_active', true)->get();
        $colorValues = $this->colorAttributeValues($product);
        $attributesUrl = url('/vendor/products/category/__ID__/attributes');

        return view('vendor.products.edit', compact('product', 'categories', 'brands', 'colorValues', 'attributesUrl'));
    }

    public function update(Request $request, Product $product)
    {
        $this->authorizeProduct($product);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:products,slug,' . $product->id,
            'description' => 'nullable|string',
            'short_description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'sku' => 'nullable|string|max:255|unique:products,sku,' . $product->id,
            'barcode' => 'nullable|string|max:255',
            'price' => 'required|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0',
            'stock_quantity' => 'nullable|integer|min:0',
            'manage_stock' => 'boolean',
            'in_stock' => 'boolean',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'tags' => 'nullable|string',
            'images' => 'nullable|array',
            'images.*' => 'image|max:2048',
        ]);

        $validated['shop_id'] = $this->shopId();

        DB::beginTransaction();
        try {
            if (empty($validated['slug'])) {
                $validated['slug'] = Str::slug($validated['name']);
            }

            $product->update($validated);

            $this->saveGalleryImages($product, $request);
            $this->saveColorImages($product, $request);
            $this->updateVariantsFromRequest($product, $request);

            if ($request->filled('primary_image_id')) {
                $this->setPrimaryImage($product, (int) $request->primary_image_id);
            }

            if ($request->has('generate_variants') && $request->generate_variants) {
                $this->generateVariants($product, $request);
            }

            $product->tags()->delete();
            if ($request->has('tags')) {
                $tags = array_filter(array_map('trim', explode(',', $request->tags)));
                foreach ($tags as $tag) {
                    $product->tags()->create(['tag' => $tag]);
                }
            }

            DB::commit();

            return redirect()->route('vendor.products.index')
                ->with('success', 'Product updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->withInput()
                ->with('error', 'Error updating product: ' . $e->getMessage());
        }
    }

    public function destroy(Product $product)
    {
        $this->authorizeProduct($product);
        $product->delete();

        return redirect()->route('vendor.products.index')
            ->with('success', 'Product deleted successfully.');
    }

    public function destroyImage(Product $product, int $image)
    {
        $this->authorizeProduct($product);
        $product->images()->findOrFail($image)->delete();

        return redirect()->back()->with('success', 'Image removed.');
    }
}
