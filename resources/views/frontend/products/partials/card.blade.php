<div class="col-lg-3 col-md-4 col-sm-6 product-grid-item">
    <div class="shop-product-card">
        <a href="{{ $product->is_customizable ? route('custom.product', $product->slug) : route('products.show', $product->slug) }}" class="shop-product-card__img-link">
            @if($product->primaryImage)
                <img src="{{ asset('storage/'.$product->primaryImage->image_path) }}" alt="{{ $product->name }}" loading="lazy">
            @else
                <div class="shop-product-card__placeholder">
                    <i class="bi bi-image text-muted display-4"></i>
                </div>
            @endif
            @if($product->discount_price)
                <span class="shop-product-card__badge">Sale</span>
            @endif
            @if($product->is_customizable)
                <span class="shop-product-card__badge shop-product-card__badge--custom">Custom</span>
            @endif
        </a>
        <div class="shop-product-card__body">
            @if($product->category)
                <span class="shop-product-card__cat">{{ $product->category->name }}</span>
            @endif
            <h2 class="shop-product-card__title">
                <a href="{{ $product->is_customizable ? route('custom.product', $product->slug) : route('products.show', $product->slug) }}">{{ $product->name }}</a>
            </h2>
            <div class="shop-product-card__price">
                @if($product->discount_price)
                    <span class="shop-product-card__price-old">{{ format_inr($product->price) }}</span>
                @endif
                <span class="shop-product-card__price-new">{{ format_inr($product->final_price) }}</span>
            </div>
            <a href="{{ $product->is_customizable ? route('custom.product', $product->slug) : route('products.show', $product->slug) }}"
               class="shop-product-card__btn {{ $product->is_customizable ? 'shop-product-card__btn--custom' : '' }}">
                @if($product->is_customizable)
                    <i class="bi bi-pencil-fill"></i> CUSTOMISE
                @else
                    <i class="bi bi-bag"></i> VIEW PRODUCT
                @endif
            </a>
        </div>
    </div>
</div>
