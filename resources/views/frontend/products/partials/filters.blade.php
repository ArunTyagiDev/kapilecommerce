@if(!empty($styleFilters) && count($styleFilters) > 1)
<div class="shop-filter-group mb-3">
    <span class="shop-filter-label">Style</span>
    <div class="shop-filter-pills">
        @foreach($styleFilters as $key => $label)
        <button type="button" class="shop-filter-pill style-pill {{ ($activeStyle ?? 'all') === $key ? 'active' : '' }}"
                data-style="{{ $key }}">{{ $label }}</button>
        @endforeach
    </div>
</div>
@endif

@foreach($categoryAttributes ?? [] as $attribute)
    @if($attribute->values->count())
    <div class="shop-filter-group mb-3">
        <span class="shop-filter-label">{{ $attribute->name }}</span>
        <div class="shop-filter-pills">
            @foreach($attribute->values as $value)
            <button type="button" class="shop-filter-pill attr-pill"
                    data-attr-id="{{ $attribute->id }}"
                    data-value-id="{{ $value->id }}">{{ $value->display_value ?? $value->value }}</button>
            @endforeach
        </div>
    </div>
    @endif
@endforeach

<div class="shop-filter-group mb-0">
    <span class="shop-filter-label">Price (₹)</span>
    <div class="d-flex gap-2 align-items-center flex-wrap">
        <input type="number" class="form-control form-control-sm shop-price-input" id="filter-min-price" placeholder="Min" value="{{ $minPrice ?? '' }}" min="0">
        <span class="text-muted">–</span>
        <input type="number" class="form-control form-control-sm shop-price-input" id="filter-max-price" placeholder="Max" value="{{ $maxPrice ?? '' }}" min="0">
    </div>
</div>
