<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ManagesProductForm;
use App\Http\Controllers\Concerns\ManagesProductVariants;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Shop;
use App\Models\Category;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    use ManagesProductVariants, ManagesProductForm;

    public function index(Request $request)
    {
        $query = Product::with(['category', 'brand', 'shop', 'images', 'variants']);

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Category filter
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Brand filter
        if ($request->has('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }

        // Status filter
        if ($request->has('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        if ($request->filled('shop_id')) {
            $query->where('shop_id', $request->shop_id);
        }

        $products = $query->orderBy('created_at', 'desc')->paginate(20);
        $categories = Category::where('is_active', true)->get();
        $brands = Brand::where('is_active', true)->get();
        $shops = Shop::with('owner')->orderBy('name')->get();

        return view('admin.products.index', compact('products', 'categories', 'brands', 'shops'));
    }

    public function create()
    {
        $categories = $this->productFormCategories();
        $brands = Brand::where('is_active', true)->get();
        $shops = Shop::where('is_active', true)->where('is_approved', true)->orderBy('name')->get();
        $attributesUrl = url('/admin/products/category/__ID__/attributes');

        return view('admin.products.create', compact('categories', 'brands', 'shops', 'attributesUrl'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:products',
            'description' => 'nullable|string',
            'short_description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'shop_id' => 'required|exists:shops,id',
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
            'is_customizable' => 'boolean',
            'customization_type' => 'nullable|in:photo,text_sticker',
            'shape_label' => 'nullable|string|max:50',
            'style_filter' => 'nullable|string|max:50',
            'omgs_source_url' => 'nullable|url|max:500',
            'allows_cod' => 'boolean',
            'tags' => 'nullable|string',
            'images' => 'nullable|array',
            'images.*' => 'image|max:2048',
        ]);

        DB::beginTransaction();
        try {
            if (empty($validated['slug'])) {
                $validated['slug'] = Str::slug($validated['name']);
            }

            $product = Product::create($validated);

            $this->saveGalleryImages($product, $request, true);
            $this->saveColorImages($product, $request);

            // Handle tags
            if ($request->has('tags')) {
                $tags = array_filter(array_map('trim', explode(',', $request->tags)));
                foreach ($tags as $tag) {
                    $product->tags()->create(['tag' => $tag]);
                }
            }

            // Generate variants if attributes are provided
            if ($request->has('generate_variants') && $request->generate_variants) {
                $this->generateVariants($product, $request);
            }

            DB::commit();

            return redirect()->route('admin.products.index')
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
        $product->load([
            'category.parent',
            'brand',
            'shop',
            'images.attributeValue',
            'tags',
            'variants.attributeValues.attribute',
        ]);
        $categories = $this->productFormCategories();
        $brands = Brand::where('is_active', true)->get();
        $shops = Shop::where('is_active', true)->where('is_approved', true)->orderBy('name')->get();
        $colorValues = $this->colorAttributeValues($product);
        $attributesUrl = url('/admin/products/category/__ID__/attributes');

        return view('admin.products.edit', compact(
            'product', 'categories', 'brands', 'shops', 'colorValues', 'attributesUrl'
        ));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:products,slug,' . $product->id,
            'description' => 'nullable|string',
            'short_description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'shop_id' => 'required|exists:shops,id',
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
            'is_customizable' => 'boolean',
            'customization_type' => 'nullable|in:photo,text_sticker',
            'shape_label' => 'nullable|string|max:50',
            'style_filter' => 'nullable|string|max:50',
            'omgs_source_url' => 'nullable|url|max:500',
            'allows_cod' => 'boolean',
            'tags' => 'nullable|string',
            'images' => 'nullable|array',
            'images.*' => 'image|max:2048',
        ]);

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

            // Handle tags
            $product->tags()->delete();
            if ($request->has('tags')) {
                $tags = array_filter(array_map('trim', explode(',', $request->tags)));
                foreach ($tags as $tag) {
                    $product->tags()->create(['tag' => $tag]);
                }
            }

            DB::commit();

            return redirect()->route('admin.products.index')
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
        $product->delete();
        return redirect()->route('admin.products.index')
            ->with('success', 'Product deleted successfully.');
    }

    public function destroyImage(Product $product, int $image)
    {
        $img = $product->images()->findOrFail($image);
        $img->delete();

        return redirect()->back()->with('success', 'Image removed.');
    }
}


