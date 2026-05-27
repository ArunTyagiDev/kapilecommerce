@extends('layouts.vendor')

@section('title', 'Create Product')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Create Product</h1>
    <a href="{{ route('vendor.products.index') }}" class="btn btn-secondary">Back</a>
</div>

<form action="{{ route('vendor.products.store') }}" method="POST" enctype="multipart/form-data" id="product-form">
    @csrf
    
    <ul class="nav nav-tabs mb-4" id="productTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="basic-tab" data-bs-toggle="tab" data-bs-target="#basic" type="button">Basic Info</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="attributes-tab" data-bs-toggle="tab" data-bs-target="#attributes" type="button">Attributes & Variants</button>
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
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="slug" class="form-label">Slug</label>
                        <input type="text" class="form-control @error('slug') is-invalid @enderror" id="slug" name="slug" value="{{ old('slug') }}">
                        @error('slug')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="short_description" class="form-label">Short Description</label>
                        <textarea class="form-control @error('short_description') is-invalid @enderror" id="short_description" name="short_description" rows="2">{{ old('short_description') }}</textarea>
                        @error('short_description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="5">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="category_id" class="form-label">Category *</label>
                        <select class="form-select @error('category_id') is-invalid @enderror" id="category_id" name="category_id" required>
                            <option value="">Select subcategory</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->parent?->name }} → {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Pick subcategory (e.g. Shoes → Women Shoes).</small>
                        @error('category_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="brand_id" class="form-label">Brand</label>
                        <select class="form-select @error('brand_id') is-invalid @enderror" id="brand_id" name="brand_id">
                            <option value="">Select Brand</option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand->id }}" {{ old('brand_id') == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                            @endforeach
                        </select>
                        @error('brand_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="sku" class="form-label">SKU</label>
                        <input type="text" class="form-control @error('sku') is-invalid @enderror" id="sku" name="sku" value="{{ old('sku') }}">
                        <small class="form-text text-muted">Leave empty to auto-generate</small>
                        @error('sku')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="price" class="form-label">Price (INR) *</label>
                        <input type="number" step="0.01" class="form-control @error('price') is-invalid @enderror" id="price" name="price" value="{{ old('price') }}" required>
                        @error('price')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="discount_price" class="form-label">Discount Price</label>
                        <input type="number" step="0.01" class="form-control @error('discount_price') is-invalid @enderror" id="discount_price" name="discount_price" value="{{ old('discount_price') }}">
                        @error('discount_price')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="stock_quantity" class="form-label">Stock Quantity</label>
                        <input type="number" class="form-control @error('stock_quantity') is-invalid @enderror" id="stock_quantity" name="stock_quantity" value="{{ old('stock_quantity', 0) }}">
                        @error('stock_quantity')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="manage_stock" name="manage_stock" value="1" {{ old('manage_stock', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="manage_stock">Manage Stock</label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="in_stock" name="in_stock" value="1" {{ old('in_stock', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="in_stock">In Stock</label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_featured">Featured</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Attributes & Variants Tab -->
        <div class="tab-pane fade" id="attributes" role="tabpanel">
            <div class="alert alert-secondary small">
                <strong>Step 1:</strong> Choose subcategory on Basic Info.<br>
                <strong>Step 2:</strong> Tick sizes &amp; colors → enable <em>Generate Variants</em>.<br>
                <strong>Step 3:</strong> Images tab — upload main photo + one per color.
            </div>
            <div id="category-attributes-container">
                <p class="text-muted">Select subcategory on Basic Info first</p>
            </div>

            <div class="mb-3 mt-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="generate_variants" name="generate_variants" value="1">
                    <label class="form-check-label" for="generate_variants">Generate Variants Automatically</label>
                </div>
                <small class="form-text text-muted">Check this to auto-generate product variants based on selected attribute values</small>
            </div>

            <div class="mb-3" id="variant-stock-section" style="display: none;">
                <label for="variant_stock_quantity" class="form-label">Default Stock Quantity for Variants</label>
                <input type="number" class="form-control" id="variant_stock_quantity" name="variant_stock_quantity" value="0">
            </div>
        </div>

        <!-- Images Tab -->
        <div class="tab-pane fade" id="images" role="tabpanel">
            <div class="alert alert-primary"><strong>Main image:</strong> first uploaded file is the listing photo.</div>
            <div class="mb-3">
                <label for="images" class="form-label">Gallery / Main images</label>
                <input type="file" class="form-control @error('images.*') is-invalid @enderror" id="images" name="images[]" accept="image/*" multiple>
                <small class="text-muted">JPG/PNG. After save, edit product to add per-color images.</small>
                @error('images.*')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div id="image-preview" class="row"></div>

            <div class="mb-3 mt-4">
                <label for="tags" class="form-label">Tags</label>
                <input type="text" class="form-control @error('tags') is-invalid @enderror" id="tags" name="tags" value="{{ old('tags') }}" placeholder="Comma separated tags">
                <small class="form-text text-muted">e.g., tag1, tag2, tag3</small>
                @error('tags')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <!-- SEO Tab -->
        <div class="tab-pane fade" id="seo" role="tabpanel">
            <div class="mb-3">
                <label for="meta_title" class="form-label">Meta Title</label>
                <input type="text" class="form-control @error('meta_title') is-invalid @enderror" id="meta_title" name="meta_title" value="{{ old('meta_title') }}" maxlength="255">
                @error('meta_title')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="meta_description" class="form-label">Meta Description</label>
                <textarea class="form-control @error('meta_description') is-invalid @enderror" id="meta_description" name="meta_description" rows="3">{{ old('meta_description') }}</textarea>
                @error('meta_description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="meta_keywords" class="form-label">Meta Keywords</label>
                <input type="text" class="form-control @error('meta_keywords') is-invalid @enderror" id="meta_keywords" name="meta_keywords" value="{{ old('meta_keywords') }}" placeholder="Comma separated keywords">
                @error('meta_keywords')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>

    <div class="mt-4">
        <button type="submit" class="btn btn-primary btn-lg">Create Product</button>
        <a href="{{ route('vendor.products.index') }}" class="btn btn-secondary btn-lg">Cancel</a>
    </div>
</form>
@endsection

@push('styles')
<style>
    #image-preview img {
        max-width: 200px;
        max-height: 200px;
        margin: 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
    }
</style>
@endpush

@push('scripts')
<script>
    document.getElementById('name').addEventListener('input', function() {
        document.getElementById('slug').value = this.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
    });
    document.getElementById('images')?.addEventListener('change', function(e) {
        const preview = document.getElementById('image-preview');
        preview.innerHTML = '';
        Array.from(e.target.files).forEach(file => {
            const reader = new FileReader();
            reader.onload = ev => {
                const img = document.createElement('img');
                img.src = ev.target.result;
                preview.appendChild(img);
            };
            reader.readAsDataURL(file);
        });
    });
</script>
@include('shared.product-attributes-script')
@endpush

