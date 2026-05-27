@extends('layouts.frontend')

@section('title', $product->name)

@push('styles')
<style>
    .product-main-image {
        max-height: 500px;
        width: 100%;
        object-fit: contain;
        background: #f8f9fa;
        border-radius: 8px;
    }
    .option-label { font-size: 0.95rem; color: #6c757d; margin-bottom: 0.5rem; }
    .size-pill {
        display: inline-block;
        min-width: 3.5rem;
        padding: 0.5rem 1rem;
        margin: 0 0.5rem 0.5rem 0;
        border: 1px solid #dee2e6;
        border-radius: 999px;
        background: #fff;
        cursor: pointer;
        text-align: center;
        font-size: 0.9rem;
        transition: all 0.15s;
    }
    .size-pill:hover:not(.disabled) { border-color: #212529; }
    .size-pill.active { background: #212529; color: #fff; border-color: #212529; }
    .size-pill.disabled { opacity: 0.4; cursor: not-allowed; text-decoration: line-through; }
    .color-swatch {
        width: 64px;
        height: 64px;
        padding: 3px;
        border: 2px solid transparent;
        border-radius: 8px;
        cursor: pointer;
        background: #fff;
        margin: 0 0.5rem 0.5rem 0;
    }
    .color-swatch.active { border-color: #212529; }
    .color-swatch img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 4px;
    }
    .color-swatch .swatch-fill {
        width: 100%;
        height: 100%;
        border-radius: 4px;
        border: 1px solid #ddd;
    }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-md-6">
        <img id="main-product-image"
             src="{{ $defaultImage ? asset('storage/'.$defaultImage->image_path) : 'https://placehold.co/600x500?text=No+Image' }}"
             alt="{{ $product->name }}"
             class="product-main-image mb-3">

        @if($product->images->whereNull('attribute_value_id')->count() > 1)
        <div class="d-flex flex-wrap gap-2">
            @foreach($product->images->whereNull('attribute_value_id') as $img)
            <img src="{{ asset('storage/'.$img->image_path) }}"
                 class="rounded border gallery-thumb"
                 style="width:72px;height:72px;object-fit:cover;cursor:pointer"
                 data-src="{{ asset('storage/'.$img->image_path) }}"
                 alt="">
            @endforeach
        </div>
        @endif
    </div>

    <div class="col-md-6">
        <h1>{{ $product->name }}</h1>
        <p class="text-muted">{{ $product->category->name }}
            @if($product->shop) · Sold by <strong>{{ $product->shop->name }}</strong> @endif
        </p>

        <div class="mb-3" id="price-display">
            @if($product->discount_price)
                <span class="price-old fs-5">₹{{ number_format($product->price, 2) }}</span>
                <span class="price-new fs-3">₹{{ number_format($product->discount_price, 2) }}</span>
            @else
                <span class="price-new fs-3">₹{{ number_format($product->price, 2) }}</span>
            @endif
        </div>

        <div class="mb-4"><p>{{ $product->description }}</p></div>

        <form action="{{ route('cart.add') }}" method="POST" id="add-to-cart-form">
            @csrf
            <input type="hidden" name="product_id" value="{{ $product->id }}">
            <input type="hidden" name="variant_id" id="selected_variant_id">

            @php
                $colorAttr = $attributes->firstWhere('slug', 'color');
                $otherAttrs = $attributes->where('slug', '!=', 'color');
            @endphp

            @if($colorAttr && $colorAttr->values->count())
            <div class="mb-4">
                <div class="option-label">Color</div>
                <div class="d-flex flex-wrap" id="color-options">
                    @foreach($colorAttr->values as $value)
                    @php
                        $imgUrl = $colorImages[$value->id] ?? null;
                    @endphp
                    <button type="button"
                            class="color-swatch"
                            data-attribute-id="{{ $colorAttr->id }}"
                            data-value-id="{{ $value->id }}"
                            data-image="{{ $imgUrl }}"
                            title="{{ $value->display_value ?? $value->value }}">
                        @if($imgUrl)
                            <img src="{{ $imgUrl }}" alt="{{ $value->display_value ?? $value->value }}">
                        @else
                            <span class="swatch-fill d-block" style="background:{{ $value->color_code ?? '#ccc' }}"></span>
                        @endif
                    </button>
                    @endforeach
                </div>
            </div>
            @endif

            @foreach($otherAttrs as $attribute)
                @if($attribute->values->count())
                <div class="mb-4">
                    <div class="option-label">{{ $attribute->name }}</div>
                    <div class="size-options" data-attribute-id="{{ $attribute->id }}">
                        @foreach($attribute->values as $value)
                        <button type="button"
                                class="size-pill"
                                data-attribute-id="{{ $attribute->id }}"
                                data-value-id="{{ $value->id }}">
                            {{ $value->display_value ?? $value->value }}
                        </button>
                        @endforeach
                    </div>
                </div>
                @endif
            @endforeach

            <div class="mb-3">
                <label class="form-label"><strong>Quantity</strong></label>
                <input type="number" class="form-control w-auto" name="quantity" value="1" min="1" id="quantity" required>
            </div>

            <div class="mb-3" id="stock-status">
                @if($product->in_stock)
                    <span class="badge bg-success">In Stock</span>
                @else
                    <span class="badge bg-danger">Out of Stock</span>
                @endif
            </div>

            <button type="submit" class="btn btn-primary btn-lg w-100" id="add-to-cart-btn" {{ !$product->in_stock ? 'disabled' : '' }}>
                <i class="bi bi-cart-plus"></i> Add to Cart
            </button>
        </form>

        <div class="mt-4">
            <ul class="list-unstyled small">
                <li><strong>SKU:</strong> {{ $product->sku }}</li>
                @if($product->brand)<li><strong>Brand:</strong> {{ $product->brand->name }}</li>@endif
            </ul>
        </div>
    </div>
</div>

@if($relatedProducts->count() > 0)
<div class="row mt-5">
    <div class="col-12">
        <h3>Related Products</h3>
        <div class="row">
            @foreach($relatedProducts as $related)
            <div class="col-md-3 mb-4">
                <div class="card product-card h-100">
                    @if($related->primaryImage)
                        <img src="{{ asset('storage/' . $related->primaryImage->image_path) }}" class="card-img-top" alt="{{ $related->name }}" style="height: 200px; object-fit: cover;">
                    @endif
                    <div class="card-body">
                        <h6 class="card-title">{{ $related->name }}</h6>
                        <p class="card-text"><span class="price-new">₹{{ number_format($related->final_price, 2) }}</span></p>
                        <a href="{{ route('products.show', $related->slug) }}" class="btn btn-sm btn-primary">View</a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
const variants = @json($variantsPayload);
const productId = {{ $product->id }};
const hasVariants = variants.length > 0;
let selected = {};

function setMainImage(url) {
    if (url) document.getElementById('main-product-image').src = url;
}

document.querySelectorAll('.gallery-thumb').forEach(thumb => {
    thumb.addEventListener('click', () => setMainImage(thumb.dataset.src));
});

document.querySelectorAll('.color-swatch').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.color-swatch').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        selected[this.dataset.attributeId] = this.dataset.valueId;
        if (this.dataset.image) setMainImage(this.dataset.image);
        refreshVariant();
    });
});

document.querySelectorAll('.size-pill').forEach(btn => {
    btn.addEventListener('click', function() {
        if (this.classList.contains('disabled')) return;
        const attrId = this.dataset.attributeId;
        this.closest('.size-options').querySelectorAll('.size-pill').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        selected[attrId] = this.dataset.valueId;
        refreshVariant();
    });
});

function findVariant() {
    const keys = Object.keys(selected);
    if (!hasVariants) return null;
    if (keys.length === 0) return null;
    return variants.find(v => {
        return keys.every(attrId => String(v.attributes[attrId]) === String(selected[attrId]));
    });
}

function refreshVariant() {
    const v = findVariant();
    const statusDiv = document.getElementById('stock-status');
    const addBtn = document.getElementById('add-to-cart-btn');
    const variantInput = document.getElementById('selected_variant_id');

    if (!hasVariants) {
        variantInput.value = '';
        return;
    }

    if (v) {
        variantInput.value = v.id;
        if (v.image) setMainImage(v.image);
        const inStock = v.in_stock && v.stock_quantity > 0;
        statusDiv.innerHTML = inStock
            ? `<span class="badge bg-success">In Stock (${v.stock_quantity} available)</span>`
            : `<span class="badge bg-danger">Out of Stock</span>`;
        addBtn.disabled = !inStock;
        document.getElementById('price-display').innerHTML =
            `<span class="price-new fs-3">₹${Number(v.price).toLocaleString('en-IN', {minimumFractionDigits: 2})}</span>`;
    } else {
        variantInput.value = '';
        statusDiv.innerHTML = '<span class="badge bg-warning text-dark">Select all options</span>';
        addBtn.disabled = true;
    }
    updateAvailableSizes();
}

function updateAvailableSizes() {
    document.querySelectorAll('.size-pill').forEach(pill => {
        const attrId = pill.dataset.attributeId;
        const valueId = pill.dataset.valueId;
        const test = {...selected, [attrId]: valueId};
        const possible = variants.some(v =>
            Object.keys(test).every(k => String(v.attributes[k]) === String(test[k]))
        );
        pill.classList.toggle('disabled', !possible);
        if (!possible) pill.classList.remove('active');
    });
}

// Auto-select first color & size
const firstColor = document.querySelector('.color-swatch');
if (firstColor) firstColor.click();
document.querySelectorAll('.size-options').forEach(group => {
    const first = group.querySelector('.size-pill:not(.disabled)');
    if (first) first.click();
});
</script>
@endpush
