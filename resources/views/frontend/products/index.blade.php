@extends('layouts.frontend')

@section('title', 'Products')

@section('content')
<div class="row">
    <!-- Sidebar Filters -->
    <div class="col-md-3">
        <div class="card mb-4">
            <div class="card-header">
                <h5>Filters</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('products.index') }}" id="filter-form">
                    <!-- Search -->
                    <div class="mb-3">
                        <label class="form-label">Search</label>
                        <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="Search products...">
                    </div>

                    <!-- Category Filter -->
                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <select class="form-select" name="category">
                            <option value="">All Categories</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->slug }}" {{ request('category') == $cat->slug ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Price Filter -->
                    <div class="mb-3">
                        <label class="form-label">Price Range</label>
                        <div class="row">
                            <div class="col-6">
                                <input type="number" class="form-control" name="min_price" value="{{ request('min_price') }}" placeholder="Min">
                            </div>
                            <div class="col-6">
                                <input type="number" class="form-control" name="max_price" value="{{ request('max_price') }}" placeholder="Max">
                            </div>
                        </div>
                    </div>

                    <!-- Attribute Filters -->
                    @foreach($filterableAttributes as $attribute)
                        @if($attribute->values->count() > 0)
                            <div class="mb-3">
                                <label class="form-label">{{ $attribute->name }}</label>
                                @foreach($attribute->values as $value)
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" 
                                               name="attr_{{ $attribute->id }}[]" 
                                               value="{{ $value->id }}"
                                               id="attr_{{ $attribute->id }}_{{ $value->id }}"
                                               {{ in_array($value->id, (array)request("attr_{$attribute->id}")) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="attr_{{ $attribute->id }}_{{ $value->id }}">
                                            {{ $value->display_value ?? $value->value }}
                                            @if($value->color_code)
                                                <span class="badge" style="background-color: {{ $value->color_code }};">&nbsp;</span>
                                            @endif
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    @endforeach

                    <!-- Sort -->
                    <div class="mb-3">
                        <label class="form-label">Sort By</label>
                        <select class="form-select" name="sort">
                            <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Latest</option>
                            <option value="price_low" {{ request('sort') == 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
                            <option value="price_high" {{ request('sort') == 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
                            <option value="name" {{ request('sort') == 'name' ? 'selected' : '' }}>Name: A to Z</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Apply Filters</button>
                    <a href="{{ route('products.index') }}" class="btn btn-secondary w-100 mt-2">Reset</a>
                </form>
            </div>
        </div>
    </div>

    <!-- Products Grid -->
    <div class="col-md-9">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Products</h2>
            <span class="text-muted">{{ $products->total() }} products found</span>
        </div>

        <div class="row">
            @forelse($products as $product)
            <div class="col-md-4 mb-4">
                <div class="card product-card h-100">
                    @if($product->primaryImage)
                        <img src="{{ asset('storage/' . $product->primaryImage->image_path) }}" class="card-img-top" alt="{{ $product->name }}" style="height: 250px; object-fit: cover;">
                    @else
                        <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 250px;">
                            <span class="text-muted">No Image</span>
                        </div>
                    @endif
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">{{ $product->name }}</h5>
                        <p class="card-text text-muted small">{{ \Illuminate\Support\Str::limit($product->short_description ?? $product->description, 100) }}</p>
                        <div class="mt-auto">
                            <div class="mb-2">
                                @if($product->discount_price)
                                    <span class="price-old">₹{{ number_format($product->price, 2) }}</span>
                                    <span class="price-new">₹{{ number_format($product->discount_price, 2) }}</span>
                                @else
                                    <span class="price-new">₹{{ number_format($product->price, 2) }}</span>
                                @endif
                            </div>
                            <a href="{{ route('products.show', $product->slug) }}" class="btn btn-primary w-100">View Details</a>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12">
                <div class="alert alert-info">No products found matching your criteria.</div>
            </div>
            @endforelse
        </div>

        {{ $products->links() }}
    </div>
</div>
@endsection

