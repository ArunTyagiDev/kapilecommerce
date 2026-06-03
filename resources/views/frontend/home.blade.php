@extends('layouts.frontend')

@section('title', 'Home')

@section('content')
<!-- Banner Slider -->
@if($banners->count() > 0)
<div id="bannerCarousel" class="carousel slide mb-5" data-bs-ride="carousel">
    <div class="carousel-inner">
        @foreach($banners as $index => $banner)
        <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
            <a href="{{ $banner->link ?? '#' }}">
                <img src="{{ asset('storage/' . $banner->image) }}" class="d-block w-100" alt="{{ $banner->title }}" style="max-height: 400px; object-fit: cover;">
                @if($banner->title || $banner->description)
                <div class="carousel-caption d-none d-md-block">
                    @if($banner->title)
                        <h2>{{ $banner->title }}</h2>
                    @endif
                    @if($banner->description)
                        <p>{{ $banner->description }}</p>
                    @endif
                </div>
                @endif
            </a>
        </div>
        @endforeach
    </div>
    @if($banners->count() > 1)
    <button class="carousel-control-prev" type="button" data-bs-target="#bannerCarousel" data-bs-slide="prev">
        <span class="carousel-control-prev-icon"></span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#bannerCarousel" data-bs-slide="next">
        <span class="carousel-control-next-icon"></span>
    </button>
    @endif
</div>
@endif

<!-- Search Bar -->
<div class="row mb-5">
    <div class="col-md-8 mx-auto">
        <form action="{{ route('products.index') }}" method="GET">
            <div class="input-group input-group-lg">
                <input type="text" class="form-control" name="search" placeholder="Search for products..." value="{{ request('search') }}">
                <button class="btn btn-primary" type="submit">
                    <i class="bi bi-search"></i> Search
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Custom Photo (OMGS-style) -->
<section class="mb-5 py-4 bg-dark text-white rounded-3">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h2 class="h3 mb-2">Custom Photo &amp; Décor</h2>
            <p class="mb-0 opacity-75">Acrylic wall photos, clocks, frames, magnets &amp; more — upload your photo, pick size &amp; thickness. Prices in ₹.</p>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
            <a href="{{ route('customise.index') }}" class="btn btn-light btn-lg">Start customising</a>
            <a href="{{ route('customise.hub', 'acrylic-wall-photo') }}" class="btn btn-outline-light ms-2">Acrylic prints</a>
        </div>
    </div>
</section>

<!-- Category Blocks -->
@if($categories->count() > 0)
<section class="mb-5">
    <h2 class="mb-4">Shop by Category</h2>
    <div class="row">
        @foreach($categories as $category)
        <div class="col-md-4 col-sm-6 mb-4">
            <div class="card h-100 category-card">
                @if($category->image)
                    <img src="{{ asset('storage/' . $category->image) }}" class="card-img-top" alt="{{ $category->name }}" style="height: 200px; object-fit: cover;">
                @else
                    <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                        <i class="bi bi-folder" style="font-size: 4rem; color: #ccc;"></i>
                    </div>
                @endif
                <div class="card-body text-center">
                    <h5 class="card-title">{{ $category->name }}</h5>
                    <a href="{{ route('products.index', ['category' => $category->slug]) }}" class="btn btn-primary">Shop Now</a>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</section>
@endif

<!-- Featured Products -->
@if($featuredProducts->count() > 0)
<section class="mb-5">
    <h2 class="mb-4">Featured Products</h2>
    <div class="row">
        @foreach($featuredProducts as $product)
        <div class="col-md-3 col-sm-6 mb-4">
            <div class="card product-card h-100">
                @if($product->primaryImage)
                    <img src="{{ asset('storage/' . $product->primaryImage->image_path) }}" class="card-img-top" alt="{{ $product->name }}" style="height: 250px; object-fit: cover;" loading="lazy">
                @else
                    <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 250px;">
                        <span class="text-muted">No Image</span>
                    </div>
                @endif
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title">{{ $product->name }}</h5>
                    <p class="card-text text-muted small">{{ \Illuminate\Support\Str::limit($product->short_description ?? $product->description, 80) }}</p>
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
        @endforeach
    </div>
</section>
@endif

<!-- Latest Products -->
@if($latestProducts->count() > 0)
<section class="mb-5">
    <h2 class="mb-4">Latest Products</h2>
    <div class="row">
        @foreach($latestProducts as $product)
        <div class="col-md-3 col-sm-6 mb-4">
            <div class="card product-card h-100">
                @if($product->primaryImage)
                    <img src="{{ asset('storage/' . $product->primaryImage->image_path) }}" class="card-img-top" alt="{{ $product->name }}" style="height: 250px; object-fit: cover;" loading="lazy">
                @else
                    <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 250px;">
                        <span class="text-muted">No Image</span>
                    </div>
                @endif
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title">{{ $product->name }}</h5>
                    <p class="card-text text-muted small">{{ \Illuminate\Support\Str::limit($product->short_description ?? $product->description, 80) }}</p>
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
        @endforeach
    </div>
</section>
@endif
@endsection

@push('styles')
<style>
    .category-card {
        transition: transform 0.2s;
    }
    .category-card:hover {
        transform: translateY(-5px);
    }
</style>
@endpush

