@extends('layouts.admin')

@section('title', 'Edit Product')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Edit Product</h1>
    <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">Back</a>
</div>

<form action="{{ route('admin.products.update', $product) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    
    <ul class="nav nav-tabs mb-4" id="productTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="basic-tab" data-bs-toggle="tab" data-bs-target="#basic" type="button">Basic Info</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="variants-tab" data-bs-toggle="tab" data-bs-target="#variants" type="button">Variants</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="images-tab" data-bs-toggle="tab" data-bs-target="#images" type="button">Images</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="seo-tab" data-bs-toggle="tab" data-bs-target="#seo" type="button">SEO</button>
        </li>
    </ul>

    <div class="tab-content" id="productTabsContent">
        <!-- Basic Info Tab -->
        <div class="tab-pane fade show active" id="basic" role="tabpanel">
            <div class="row">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label for="name" class="form-label">Product Name *</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $product->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="slug" class="form-label">Slug</label>
                        <input type="text" class="form-control @error('slug') is-invalid @enderror" id="slug" name="slug" value="{{ old('slug', $product->slug) }}">
                        @error('slug')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="short_description" class="form-label">Short Description</label>
                        <textarea class="form-control @error('short_description') is-invalid @enderror" id="short_description" name="short_description" rows="2">{{ old('short_description', $product->short_description) }}</textarea>
                        @error('short_description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="5">{{ old('description', $product->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="category_id" class="form-label">Category *</label>
                        <select class="form-select @error('category_id') is-invalid @enderror" id="category_id" name="category_id" required>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->parent?->name }} → {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="shop_id" class="form-label">Vendor Shop *</label>
                        <select class="form-select @error('shop_id') is-invalid @enderror" id="shop_id" name="shop_id" required>
                            <option value="">Select Vendor</option>
                            @foreach($shops as $shop)
                                <option value="{{ $shop->id }}" {{ old('shop_id', $product->shop_id) == $shop->id ? 'selected' : '' }}>{{ $shop->name }}</option>
                            @endforeach
                        </select>
                        @error('shop_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="brand_id" class="form-label">Brand</label>
                        <select class="form-select @error('brand_id') is-invalid @enderror" id="brand_id" name="brand_id">
                            <option value="">Select Brand</option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand->id }}" {{ old('brand_id', $product->brand_id) == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                            @endforeach
                        </select>
                        @error('brand_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="sku" class="form-label">SKU</label>
                        <input type="text" class="form-control @error('sku') is-invalid @enderror" id="sku" name="sku" value="{{ old('sku', $product->sku) }}">
                        @error('sku')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="price" class="form-label">Price *</label>
                        <input type="number" step="0.01" class="form-control @error('price') is-invalid @enderror" id="price" name="price" value="{{ old('price', $product->price) }}" required>
                        @error('price')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="discount_price" class="form-label">Discount Price</label>
                        <input type="number" step="0.01" class="form-control @error('discount_price') is-invalid @enderror" id="discount_price" name="discount_price" value="{{ old('discount_price', $product->discount_price) }}">
                        @error('discount_price')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="stock_quantity" class="form-label">Stock Quantity</label>
                        <input type="number" class="form-control @error('stock_quantity') is-invalid @enderror" id="stock_quantity" name="stock_quantity" value="{{ old('stock_quantity', $product->stock_quantity) }}">
                        @error('stock_quantity')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="manage_stock" name="manage_stock" value="1" {{ old('manage_stock', $product->manage_stock) ? 'checked' : '' }}>
                            <label class="form-check-label" for="manage_stock">Manage Stock</label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="in_stock" name="in_stock" value="1" {{ old('in_stock', $product->in_stock) ? 'checked' : '' }}>
                            <label class="form-check-label" for="in_stock">In Stock</label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $product->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" value="1" {{ old('is_featured', $product->is_featured) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_featured">Featured</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Variants Tab -->
        <div class="tab-pane fade" id="variants" role="tabpanel">
            <div class="card mb-4">
                <div class="card-header">Add more variants (size × color)</div>
                <div class="card-body">
                    <div id="category-attributes-container">
                        <p class="text-muted small">Change category on Basic Info if needed, then select attribute values:</p>
                    </div>
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" id="generate_variants" name="generate_variants" value="1">
                        <label class="form-check-label" for="generate_variants">Generate new variants from checked values</label>
                    </div>
                    <div class="mb-3 mt-2" id="variant-stock-section" style="display:none;">
                        <label class="form-label">Default stock per new variant</label>
                        <input type="number" class="form-control w-auto" name="variant_stock_quantity" value="10" min="0">
                    </div>
                </div>
            </div>

            <h5 class="mb-3">Existing variants (update stock &amp; photo)</h5>
            @if($product->variants->count() > 0)
                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>SKU</th>
                                <th>Size / Color</th>
                                <th>Stock</th>
                                <th>Variant image</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($product->variants as $variant)
                            <tr>
                                <td><small>{{ $variant->sku }}</small></td>
                                <td>
                                    @foreach($variant->attributeValues as $value)
                                        <span class="badge bg-secondary">{{ $value->display_value ?? $value->value }}</span>
                                    @endforeach
                                </td>
                                <td style="width:100px">
                                    <input type="number" class="form-control form-control-sm" name="variants[{{ $variant->id }}][stock_quantity]" value="{{ $variant->stock_quantity }}" min="0">
                                </td>
                                <td>
                                    @if($variant->image)
                                        <img src="{{ asset('storage/'.$variant->image) }}" height="40" class="me-2 rounded">
                                    @endif
                                    <input type="file" class="form-control form-control-sm" name="variants[{{ $variant->id }}][image]" accept="image/*">
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted">No variants yet — use the generator above or recreate product with variants on create.</p>
            @endif
        </div>

        <!-- Images Tab -->
        <div class="tab-pane fade" id="images" role="tabpanel">
            <div class="alert alert-primary py-2">
                <strong>Main image:</strong> mark one gallery image as Primary. <strong>Color images:</strong> one photo per color — storefront switches image when customer picks a color.
            </div>

            <h6 class="mt-3">Gallery</h6>
            <div class="row mb-3">
                @forelse($product->images->whereNull('attribute_value_id') as $image)
                <div class="col-md-3 mb-3 text-center">
                    <img src="{{ asset('storage/' . $image->image_path) }}" class="img-fluid rounded border">
                    <div class="mt-1">
                        <label class="small">
                            <input type="radio" name="primary_image_id" value="{{ $image->id }}" {{ $image->is_primary ? 'checked' : '' }}> Primary
                        </label>
                        <form action="{{ route('admin.products.images.destroy', [$product, $image]) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">×</button>
                        </form>
                    </div>
                </div>
                @empty
                <p class="text-muted col-12">No gallery images — upload below.</p>
                @endforelse
            </div>
            <div class="mb-4">
                <label class="form-label">Add gallery / main images</label>
                <input type="file" class="form-control" name="images[]" accept="image/*" multiple>
            </div>

            @if($colorValues->isNotEmpty())
            <h6>Image per color (for storefront color switcher)</h6>
            <div class="row">
                @foreach($colorValues as $color)
                @php $colorImg = $product->images->firstWhere('attribute_value_id', $color->id); @endphp
                <div class="col-md-4 mb-3">
                    <label class="form-label d-flex align-items-center gap-2">
                        @if($color->color_code)
                        <span class="rounded-circle border" style="width:18px;height:18px;background:{{ $color->color_code }}"></span>
                        @endif
                        {{ $color->display_value ?? $color->value }}
                    </label>
                    @if($colorImg)
                        <img src="{{ asset('storage/'.$colorImg->image_path) }}" class="img-fluid rounded mb-2" style="max-height:80px">
                    @endif
                    <input type="file" class="form-control form-control-sm" name="color_images[{{ $color->id }}]" accept="image/*">
                </div>
                @endforeach
            </div>
            @endif

            <div class="mb-3 mt-3">
                <label for="tags" class="form-label">Tags</label>
                <input type="text" class="form-control" id="tags" name="tags" value="{{ old('tags', $product->tags->pluck('tag')->implode(', ')) }}">
            </div>
        </div>

        <!-- SEO Tab -->
        <div class="tab-pane fade" id="seo" role="tabpanel">
            <div class="mb-3">
                <label for="meta_title" class="form-label">Meta Title</label>
                <input type="text" class="form-control" id="meta_title" name="meta_title" value="{{ old('meta_title', $product->meta_title) }}" maxlength="255">
            </div>

            <div class="mb-3">
                <label for="meta_description" class="form-label">Meta Description</label>
                <textarea class="form-control" id="meta_description" name="meta_description" rows="3">{{ old('meta_description', $product->meta_description) }}</textarea>
            </div>

            <div class="mb-3">
                <label for="meta_keywords" class="form-label">Meta Keywords</label>
                <input type="text" class="form-control" id="meta_keywords" name="meta_keywords" value="{{ old('meta_keywords', $product->meta_keywords) }}" placeholder="Comma separated keywords">
            </div>
        </div>
    </div>

    <div class="mt-4">
        <button type="submit" class="btn btn-primary btn-lg">Update Product</button>
        <a href="{{ route('admin.products.index') }}" class="btn btn-secondary btn-lg">Cancel</a>
    </div>
</form>
@endsection

@include('shared.product-attributes-script')

