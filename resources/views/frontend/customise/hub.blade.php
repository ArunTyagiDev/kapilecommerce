@extends('layouts.frontend')

@section('title', $category->name . ' — Customise')

@push('styles')
<style>
    .omgs-filter-bar {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        padding: 0.75rem 0 1.25rem;
        border-bottom: 1px solid #eee;
        margin-bottom: 1.5rem;
    }
    .omgs-filter-pill {
        display: inline-block;
        padding: 0.45rem 1.1rem;
        border-radius: 999px;
        border: 1px solid #e0e0e0;
        background: #fff;
        color: #333;
        font-size: 0.9rem;
        text-decoration: none;
        transition: all 0.15s;
    }
    .omgs-filter-pill:hover { border-color: #333; color: #111; }
    .omgs-filter-pill.active {
        background: #e63946;
        border-color: #e63946;
        color: #fff;
    }
    .omgs-product-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0,0,0,.08);
        overflow: hidden;
        height: 100%;
        display: flex;
        flex-direction: column;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .omgs-product-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 24px rgba(0,0,0,.12);
    }
    .omgs-product-card img {
        width: 100%;
        height: 220px;
        object-fit: contain;
        background: #f8f9fa;
        padding: 0.5rem;
    }
    .omgs-product-card .card-body {
        padding: 1rem 1rem 1.25rem;
        text-align: center;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
    }
    .omgs-product-card .card-title {
        font-size: 0.95rem;
        font-weight: 600;
        color: #222;
        margin-bottom: 1rem;
        flex-grow: 1;
    }
    .omgs-customise-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.35rem;
        width: 100%;
        padding: 0.65rem 1rem;
        background: #fff5f6;
        border: 1px solid #ffc9cf;
        border-radius: 8px;
        color: #e63946;
        font-weight: 600;
        font-size: 0.8rem;
        letter-spacing: 0.04em;
        text-decoration: none;
    }
    .omgs-customise-btn:hover {
        background: #e63946;
        color: #fff;
        border-color: #e63946;
    }
    .line-tabs .nav-link {
        color: #555;
        border-radius: 999px;
        margin-right: 0.35rem;
        font-size: 0.9rem;
    }
    .line-tabs .nav-link.active {
        background: #212529;
        color: #fff;
    }
</style>
@endpush

@section('content')
<nav aria-label="breadcrumb" class="mb-2">
    <ol class="breadcrumb small mb-0">
        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('customise.index') }}">Custom Photo</a></li>
        <li class="breadcrumb-item active">{{ $category->name }}</li>
    </ol>
</nav>

<h1 class="h3 mb-1">{{ $category->name }}</h1>
<p class="text-muted small mb-3">Choose a shape — then upload your photo on the next page (OMGS-style customiser).</p>

@if($siblingCategories->count() > 1)
<ul class="nav line-tabs flex-nowrap overflow-auto pb-2 mb-0">
    @foreach($siblingCategories as $sib)
    <li class="nav-item">
        <a class="nav-link {{ $sib->id === $category->id ? 'active' : '' }}"
           href="{{ route('customise.hub', $sib->hub_route_slug ?? $sib->slug) }}">
            {{ $sib->name }}
        </a>
    </li>
    @endforeach
</ul>
@endif

<div class="omgs-filter-bar">
    @foreach($availableFilters as $key => $label)
    <a href="{{ route('customise.hub', $hubSlug) }}?style={{ $key }}"
       class="omgs-filter-pill {{ $styleFilter === $key ? 'active' : '' }}">
        {{ $label }}
    </a>
    @endforeach
</div>

<div class="row g-4" id="product-grid">
    @forelse($products as $product)
    <div class="col-lg-3 col-md-4 col-sm-6 product-item" data-style="{{ $product->style_filter }}">
        <div class="omgs-product-card">
            @if($product->primaryImage)
                <img src="{{ asset('storage/'.$product->primaryImage->image_path) }}" alt="{{ $product->name }}" loading="lazy">
            @else
                <div class="d-flex align-items-center justify-content-center bg-light" style="height:220px">
                    <i class="bi bi-image text-muted display-4"></i>
                </div>
            @endif
            <div class="card-body">
                <h2 class="card-title">{{ $product->name }}</h2>
                @if($product->discount_price)
                <p class="small mb-2">
                    <span class="text-muted text-decoration-line-through">{{ format_inr($product->price) }}</span>
                    <span class="fw-bold text-danger">{{ format_inr($product->final_price) }}</span>
                </p>
                @endif
                <a href="{{ route('custom.product', $product->slug) }}" class="omgs-customise-btn">
                    <i class="bi bi-pencil-fill"></i> CUSTOMISE
                </a>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <p class="text-muted">No products in this filter. <a href="{{ route('customise.hub', $hubSlug) }}?style=all">Show all</a></p>
    </div>
    @endforelse
</div>
@endsection
