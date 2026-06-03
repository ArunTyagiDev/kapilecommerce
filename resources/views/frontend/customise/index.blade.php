@extends('layouts.frontend')

@section('title', 'Custom Photo & Décor')

@section('content')
<div class="text-center mb-5">
    <h1 class="display-5 fw-bold">Custom Photo &amp; Décor</h1>
    <p class="lead text-muted">Upload your photo — we print on premium acrylic, frames, clocks &amp; more. Prices in ₹ (INR).</p>
</div>

@if($lines->count())
<section class="mb-5">
    <h2 class="h4 mb-4">Choose your product line</h2>
    <div class="row g-4">
        @foreach($lines as $line)
        <div class="col-md-4 col-sm-6">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body text-center p-4">
                    <div class="rounded-circle bg-primary bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-3" style="width:72px;height:72px">
                        <i class="bi bi-image text-primary fs-2"></i>
                    </div>
                    <h5 class="card-title">{{ $line->name }}</h5>
                    <p class="card-text small text-muted">Personalised prints — select shape &amp; size</p>
                    <a href="{{ route('customise.hub', $line->hub_route_slug ?? $line->slug) }}" class="btn btn-dark">Shop now</a>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</section>
@endif

@if($featuredCustom->count())
<section>
    <h2 class="h4 mb-4">Popular styles</h2>
    <div class="row">
        @foreach($featuredCustom as $product)
        <div class="col-md-3 col-6 mb-4">
            <div class="card h-100 product-card">
                @if($product->primaryImage)
                    <img src="{{ asset('storage/'.$product->primaryImage->image_path) }}" class="card-img-top" alt="{{ $product->name }}" style="height:160px;object-fit:cover">
                @else
                    <div class="bg-light d-flex align-items-center justify-content-center" style="height:160px"><i class="bi bi-image text-muted fs-1"></i></div>
                @endif
                <div class="card-body p-3">
                    <h6 class="card-title small">{{ $product->name }}</h6>
                    <p class="mb-2"><span class="price-new">{{ format_inr($product->final_price) }}</span></p>
                    <a href="{{ route('custom.product', $product->slug) }}" class="btn btn-sm btn-primary w-100">Customise</a>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</section>
@endif
@endsection
