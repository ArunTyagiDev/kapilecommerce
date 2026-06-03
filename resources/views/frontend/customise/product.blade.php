@extends('layouts.frontend')

@section('title', $product->name . ' — Customise')

@push('styles')
<style>
    .custom-frame {
        background: linear-gradient(135deg, #f5f5f5 0%, #e8e8e8 100%);
        border-radius: 12px;
        min-height: 380px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        position: relative;
        border: 2px dashed #ccc;
    }
    .custom-frame.portrait { aspect-ratio: 3/4; max-height: 420px; }
    .custom-frame.landscape { aspect-ratio: 4/3; max-height: 320px; }
    .custom-frame.square { aspect-ratio: 1/1; max-height: 360px; }
    .custom-frame.circle { border-radius: 50%; aspect-ratio: 1/1; max-width: 360px; margin: 0 auto; }
    #preview-image { max-width: 100%; max-height: 100%; object-fit: contain; display: none; }
    #preview-placeholder { color: #888; text-align: center; padding: 2rem; }
    .size-pill, .thick-pill {
        display: inline-block; padding: 0.5rem 1rem; margin: 0 0.5rem 0.5rem 0;
        border: 1px solid #dee2e6; border-radius: 999px; background: #fff;
        cursor: pointer; font-size: 0.85rem; transition: all 0.15s;
    }
    .size-pill.active, .thick-pill.active { background: #212529; color: #fff; border-color: #212529; }
    .size-pill.disabled { opacity: 0.35; cursor: not-allowed; text-decoration: line-through; }
    .upload-zone {
        border: 2px dashed #0d6efd; border-radius: 8px; padding: 1.5rem;
        text-align: center; cursor: pointer; background: #f8f9ff;
    }
    .upload-zone:hover { background: #eef3ff; }
    .style-thumb { width: 64px; height: 64px; object-fit: cover; border-radius: 6px; border: 2px solid transparent; }
    .style-thumb.active { border-color: #212529; }
</style>
@endpush

@section('content')
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb small">
        <li class="breadcrumb-item"><a href="{{ route('customise.hub', $product->category->hub_route_slug ?? $product->category->slug) }}">{{ $product->category->name }}</a></li>
        <li class="breadcrumb-item active">{{ $product->name }}</li>
    </ol>
</nav>

<div class="row">
    <div class="col-lg-6 mb-4">
        @if($product->primaryImage)
        <div class="mb-3 text-center d-none d-lg-block">
            <img src="{{ asset('storage/'.$product->primaryImage->image_path) }}" class="img-fluid rounded shadow-sm" style="max-height:120px" alt="{{ $product->name }}">
        </div>
        @endif
        @php
            $isTextSticker = ($product->customization_type ?? 'photo') === 'text_sticker';
            $shapeClass = match(strtolower($product->shape_label ?? 'portrait')) {
                'landscape' => 'landscape',
                'square', 'sticker sheet' => 'square',
                'circle' => 'circle',
                default => 'portrait',
            };
        @endphp
        @if($isTextSticker && $defaultImage)
        <div class="custom-frame square mb-2" style="border-style:solid;border-color:#dee2e6">
            <img src="{{ asset('storage/'.$defaultImage->image_path) }}" alt="{{ $product->name }}" style="display:block;max-width:100%;max-height:100%;object-fit:contain">
        </div>
        <p class="small text-muted">Design preview — your name &amp; details will be printed on 30 stickers.</p>
        @else
        <div class="custom-frame {{ $shapeClass }}" id="preview-frame">
            <img id="preview-image" alt="Your photo preview">
            <div id="preview-placeholder">
                <i class="bi bi-cloud-upload fs-1 d-block mb-2"></i>
                Upload your photo to preview
            </div>
        </div>
        @endif
        @if($defaultImage)
            <p class="small text-muted mt-2">Sample product look:</p>
            <img src="{{ asset('storage/'.$defaultImage->image_path) }}" class="img-fluid rounded" style="max-height:80px" alt="Sample">
        @endif
    </div>

    <div class="col-lg-6">
        <h1 class="h3">{{ $product->name }}</h1>
        @if($product->shape_label)
            <span class="badge bg-secondary mb-2">{{ $product->shape_label }} shape</span>
        @endif
        <div class="mb-3" id="price-display">
            @if($product->discount_price)
                <span class="text-muted text-decoration-line-through me-2">{{ format_inr($product->price) }}</span>
            @endif
            <span class="price-new fs-3 fw-bold">{{ format_inr($product->final_price) }}</span>
            <span class="text-muted small">{{ $isTextSticker ? ' per pack (30 pcs)' : ' onwards (INR)' }}</span>
        </div>

        <div class="row g-2 mb-4 small text-center">
            <div class="col-4"><div class="border rounded p-2"><i class="bi bi-truck d-block fs-5 text-success"></i> Free Shipping</div></div>
            <div class="col-4"><div class="border rounded p-2"><i class="bi bi-arrow-repeat d-block fs-5 text-primary"></i> 30-Day Returns</div></div>
            <div class="col-4"><div class="border rounded p-2"><i class="bi bi-shield-check d-block fs-5 text-info"></i> Secure Pay</div></div>
        </div>

        <form action="{{ route('cart.add') }}" method="POST" enctype="multipart/form-data" id="custom-cart-form">
            @csrf
            <input type="hidden" name="product_id" value="{{ $product->id }}">
            <input type="hidden" name="variant_id" id="selected_variant_id">
            <input type="hidden" name="size_label" id="size_label_input">
            <input type="hidden" name="thickness_label" id="thickness_label_input">

            @if($isTextSticker)
            <div class="mb-4">
                <label class="form-label fw-semibold">1. Student details <span class="text-danger">*</span></label>
                <div class="row g-2">
                    <div class="col-md-6">
                        <input type="text" class="form-control" name="student_name" id="student_name" placeholder="Student full name" required maxlength="120">
                    </div>
                    <div class="col-md-6">
                        <input type="text" class="form-control" name="student_class" id="student_class" placeholder="Class / Section (e.g. 5-A)" required maxlength="80">
                    </div>
                    <div class="col-md-6">
                        <input type="text" class="form-control" name="school_name" id="school_name" placeholder="School name (optional)" maxlength="150">
                    </div>
                    <div class="col-md-6">
                        <input type="text" class="form-control" name="contact_phone" id="contact_phone" placeholder="Parent phone (optional)" maxlength="20">
                    </div>
                </div>
                <p class="small text-muted mt-2 mb-0">30 waterproof name labels per pack — printed exactly as entered.</p>
            </div>
            <input type="hidden" name="variant_id" value="{{ $product->variants->first()?->id }}">
            @else
            <div class="mb-4">
                <label class="form-label fw-semibold">1. Upload your photo <span class="text-danger">*</span></label>
                <label class="upload-zone d-block mb-0" for="custom_photo">
                    <i class="bi bi-camera fs-4"></i>
                    <div class="mt-2">Click to upload (JPG, PNG — high resolution)</div>
                    <small class="text-muted d-block">Do not use screenshots or blurry images</small>
                </label>
                <input type="file" class="d-none" id="custom_photo" name="custom_photo" accept="image/jpeg,image/png,image/webp" required>
            </div>
            @endif

            @php
                $sizeAttr = $attributes->first(fn ($a) => in_array($a->slug, ['print-size', 'size']));
                $thickAttr = $attributes->first(fn ($a) => $a->slug === 'thickness');
                $packAttr = $attributes->first(fn ($a) => $a->slug === 'pack-size');
            @endphp

            @if($packAttr && $packAttr->values->count() && $isTextSticker)
            <div class="mb-4">
                <label class="form-label fw-semibold">2. Pack</label>
                <span class="badge bg-dark fs-6">{{ $packAttr->values->first()->display_value ?? $packAttr->values->first()->value }}</span>
            </div>
            @endif

            @if($sizeAttr && $sizeAttr->values->count() && !$isTextSticker)
            <div class="mb-4">
                <label class="form-label fw-semibold">2. Size (inches)</label>
                <div id="size-options">
                    @foreach($sizeAttr->values as $value)
                    <button type="button" class="size-pill"
                            data-attribute-id="{{ $sizeAttr->id }}"
                            data-value-id="{{ $value->id }}"
                            data-label="{{ $value->display_value ?? $value->value }}">
                        {{ $value->display_value ?? $value->value }}
                    </button>
                    @endforeach
                </div>
            </div>
            @endif

            @if($thickAttr && $thickAttr->values->count() && !$isTextSticker)
            <div class="mb-4">
                <label class="form-label fw-semibold">3. Acrylic thickness</label>
                <div id="thickness-options">
                    @foreach($thickAttr->values as $value)
                    <button type="button" class="thick-pill"
                            data-attribute-id="{{ $thickAttr->id }}"
                            data-value-id="{{ $value->id }}"
                            data-label="{{ $value->display_value ?? $value->value }}">
                        {{ $value->display_value ?? $value->value }}
                    </button>
                    @endforeach
                </div>
            </div>
            @endif

            <div class="mb-3">
                <label class="form-label">Quantity</label>
                <input type="number" name="quantity" value="1" min="1" class="form-control w-auto">
            </div>

            <div class="mb-3" id="stock-status">
                <span class="badge bg-warning text-dark">Select size{{ $thickAttr ? ' & thickness' : '' }} after upload</span>
            </div>

            <button type="submit" class="btn btn-dark btn-lg w-100" id="add-to-cart-btn" @if(!$isTextSticker) disabled @endif>
                <i class="bi bi-cart-plus"></i> Add to Cart
            </button>

            @unless($product->allows_cod)
            <p class="small text-muted mt-2"><i class="bi bi-info-circle"></i> This product is not eligible for Cash on Delivery.</p>
            @endunless
        </form>

        <div class="row g-2 mt-4 small text-muted">
            <div class="col-4 text-center"><i class="bi bi-truck d-block fs-5"></i> Free shipping ₹500+</div>
            <div class="col-4 text-center"><i class="bi bi-arrow-repeat d-block fs-5"></i> 30-day returns</div>
            <div class="col-4 text-center"><i class="bi bi-shield-check d-block fs-5"></i> Secure checkout</div>
        </div>
    </div>
</div>

@if($relatedStyles->count())
<div class="mt-5">
    <h5 class="mb-3">More {{ $product->category->name }} styles</h5>
    <div class="d-flex flex-wrap gap-2">
        @foreach($relatedStyles as $style)
        <a href="{{ route('custom.product', $style->slug) }}" class="text-decoration-none" title="{{ $style->name }}">
            @if($style->primaryImage)
                <img src="{{ asset('storage/'.$style->primaryImage->image_path) }}" class="style-thumb {{ $style->id === $product->id ? 'active' : '' }}" alt="">
            @else
                <span class="badge bg-light text-dark border p-2">{{ \Illuminate\Support\Str::limit($style->name, 20) }}</span>
            @endif
        </a>
        @endforeach
    </div>
</div>
@endif

<div class="mt-5">
    <h5>Product details</h5>
    <div class="text-muted">{!! nl2br(e($product->description)) !!}</div>
    <p class="small mt-2">Processing: {{ $product->processing_days_min }}–{{ $product->processing_days_max }} working days + shipping across India.</p>
</div>
@endsection

@push('scripts')
<script>
const variants = @json($variantsPayload);
const hasVariants = variants.length > 0;
const isTextSticker = @json($isTextSticker);
let selected = {};
let photoUploaded = false;

function validateTextSticker() {
    const name = document.getElementById('student_name')?.value.trim();
    const cls = document.getElementById('student_class')?.value.trim();
    const btn = document.getElementById('add-to-cart-btn');
    if (btn) btn.disabled = !(name && cls);
}

if (isTextSticker) {
    ['student_name', 'student_class'].forEach(id => {
        document.getElementById(id)?.addEventListener('input', validateTextSticker);
    });
    validateTextSticker();
}

const photoInput = document.getElementById('custom_photo');
if (photoInput) photoInput.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (!file) return;
    photoUploaded = true;
    const reader = new FileReader();
    reader.onload = ev => {
        const img = document.getElementById('preview-image');
        img.src = ev.target.result;
        img.style.display = 'block';
        document.getElementById('preview-placeholder').style.display = 'none';
    };
    reader.readAsDataURL(file);
    validateReady();
});

document.querySelectorAll('.size-pill').forEach(btn => {
    btn.addEventListener('click', function() {
        if (this.classList.contains('disabled')) return;
        document.querySelectorAll('.size-pill').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        selected[this.dataset.attributeId] = this.dataset.valueId;
        document.getElementById('size_label_input').value = this.dataset.label;
        refreshVariant();
    });
});

document.querySelectorAll('.thick-pill').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.thick-pill').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        selected[this.dataset.attributeId] = this.dataset.valueId;
        document.getElementById('thickness_label_input').value = this.dataset.label;
        refreshVariant();
    });
});

function findVariant() {
    const keys = Object.keys(selected);
    if (!hasVariants || keys.length === 0) return null;
    return variants.find(v => keys.every(k => String(v.attributes[k]) === String(selected[k])));
}

function refreshVariant() {
    const v = findVariant();
    const btn = document.getElementById('add-to-cart-btn');
    const status = document.getElementById('stock-status');
    if (v) {
        document.getElementById('selected_variant_id').value = v.id;
        status.innerHTML = v.in_stock && v.stock_quantity > 0
            ? `<span class="badge bg-success">In stock — ${v.stock_quantity} left</span>`
            : `<span class="badge bg-danger">Out of stock</span>`;
        document.getElementById('price-display').innerHTML =
            `<span class="price-new fs-4">₹${Number(v.price).toLocaleString('en-IN', {minimumFractionDigits: 2})}</span>`;
    } else if (hasVariants) {
        document.getElementById('selected_variant_id').value = '';
        status.innerHTML = '<span class="badge bg-warning text-dark">Select all options</span>';
    }
    validateReady();
    updateAvailableSizes();
}

function validateReady() {
    if (isTextSticker) return;
    const btn = document.getElementById('add-to-cart-btn');
    const variantOk = !hasVariants || !!findVariant();
    btn.disabled = !(photoUploaded && variantOk);
}

function updateAvailableSizes() {
    document.querySelectorAll('.size-pill').forEach(pill => {
        const test = {...selected, [pill.dataset.attributeId]: pill.dataset.valueId};
        const possible = !hasVariants || variants.some(v =>
            Object.keys(test).every(k => String(v.attributes[k]) === String(test[k]))
        );
        pill.classList.toggle('disabled', !possible);
    });
}

// Auto-select first thickness if only one dimension needed
const firstThick = document.querySelector('.thick-pill');
if (firstThick) firstThick.click();
</script>
@endpush
