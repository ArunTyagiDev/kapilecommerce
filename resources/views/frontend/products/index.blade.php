@extends('layouts.frontend')

@section('title', 'Shop All Products')

@push('styles')
<style>
    .shop-page { background: #f5f5f7; border-radius: 16px; padding: 1.25rem 1rem 2.5rem; min-height: 50vh; }
    .shop-hero {
        background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
        border-radius: 16px;
        color: #fff;
        padding: 2rem 1.75rem;
        margin-bottom: 1.5rem;
    }
    .shop-hero h1 { font-size: 1.75rem; font-weight: 700; margin-bottom: 0.35rem; }
    .shop-search {
        max-width: 420px;
        border-radius: 999px;
        border: none;
        padding: 0.65rem 1.25rem;
    }
    .shop-toolbar {
        background: #fff;
        border-radius: 12px;
        padding: 1rem 1.25rem;
        box-shadow: 0 2px 12px rgba(0,0,0,.06);
        margin-bottom: 1.25rem;
    }
    .shop-cat-pill {
        display: inline-block;
        padding: 0.4rem 1rem;
        border-radius: 999px;
        border: 1px solid #dee2e6;
        background: #fff;
        color: #333;
        font-size: 0.85rem;
        text-decoration: none;
        margin: 0 0.35rem 0.35rem 0;
        transition: all 0.15s;
        cursor: pointer;
    }
    .shop-cat-pill:hover, .shop-cat-pill.active {
        background: #212529;
        border-color: #212529;
        color: #fff;
    }
    .shop-filter-pill {
        display: inline-block;
        padding: 0.35rem 0.9rem;
        border-radius: 999px;
        border: 1px solid #e0e0e0;
        background: #fff;
        color: #444;
        font-size: 0.82rem;
        margin: 0 0.35rem 0.35rem 0;
        cursor: pointer;
        transition: all 0.15s;
    }
    .shop-filter-pill:hover { border-color: #333; }
    .shop-filter-pill.active {
        background: #e63946;
        border-color: #e63946;
        color: #fff;
    }
    .shop-filter-label {
        display: block;
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #888;
        margin-bottom: 0.4rem;
    }
    .shop-price-input { width: 100px; }
    .shop-product-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0,0,0,.08);
        overflow: hidden;
        height: 100%;
        display: flex;
        flex-direction: column;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .shop-product-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 24px rgba(0,0,0,.12);
    }
    .shop-product-card__img-link { display: block; position: relative; }
    .shop-product-card img {
        width: 100%;
        height: 220px;
        object-fit: contain;
        background: #f8f9fa;
        padding: 0.5rem;
    }
    .shop-product-card__placeholder {
        height: 220px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f8f9fa;
    }
    .shop-product-card__badge {
        position: absolute;
        top: 10px;
        left: 10px;
        background: #e63946;
        color: #fff;
        font-size: 0.65rem;
        font-weight: 700;
        padding: 0.2rem 0.5rem;
        border-radius: 4px;
        text-transform: uppercase;
    }
    .shop-product-card__badge--custom { left: auto; right: 10px; background: #212529; }
    .shop-product-card__body {
        padding: 1rem 1rem 1.25rem;
        text-align: center;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
    }
    .shop-product-card__cat {
        font-size: 0.7rem;
        color: #888;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
    .shop-product-card__title {
        font-size: 0.92rem;
        font-weight: 600;
        margin: 0.35rem 0 0.5rem;
        flex-grow: 1;
    }
    .shop-product-card__title a { color: #222; text-decoration: none; }
    .shop-product-card__title a:hover { color: #e63946; }
    .shop-product-card__price-old {
        text-decoration: line-through;
        color: #999;
        font-size: 0.8rem;
        margin-right: 0.35rem;
    }
    .shop-product-card__price-new {
        color: #e63946;
        font-weight: 700;
        font-size: 1rem;
    }
    .shop-product-card__btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.35rem;
        width: 100%;
        padding: 0.6rem 1rem;
        background: #212529;
        border-radius: 8px;
        color: #fff;
        font-weight: 600;
        font-size: 0.78rem;
        letter-spacing: 0.03em;
        text-decoration: none;
        margin-top: 0.5rem;
    }
    .shop-product-card__btn:hover { background: #000; color: #fff; }
    .shop-product-card__btn--custom {
        background: #fff5f6;
        border: 1px solid #ffc9cf;
        color: #e63946;
    }
    .shop-product-card__btn--custom:hover {
        background: #e63946;
        color: #fff;
        border-color: #e63946;
    }
    #load-sentinel { height: 1px; }
    .shop-loading { text-align: center; padding: 2rem; color: #888; }
    .shop-count { font-size: 0.9rem; color: #666; }
</style>
@endpush

@section('content')
<div class="shop-page">
    <div class="shop-hero">
        <h1>Shop All Products</h1>
        <p class="mb-3 opacity-75">Custom prints, fashion, electronics &amp; more — prices in ₹</p>
        <form id="shop-search-form" class="d-flex gap-2 flex-wrap" role="search">
            <input type="search" class="form-control shop-search flex-grow-1" name="search" id="shop-search"
                   placeholder="Search products…" value="{{ request('search') }}" autocomplete="off">
            <button type="submit" class="btn btn-light rounded-pill px-4">Search</button>
        </form>
    </div>

    <div class="shop-toolbar">
        <div class="mb-3">
            <span class="shop-filter-label">Category</span>
            <div class="d-flex flex-wrap align-items-center">
                <button type="button" class="shop-cat-pill category-pill {{ !$activeCategory ? 'active' : '' }}" data-category="">All</button>
                @foreach($rootCategories as $cat)
                <button type="button" class="shop-cat-pill category-pill {{ $activeCategory && ($activeCategory->id === $cat->id || $activeCategory->parent_id === $cat->id) ? 'active' : '' }}"
                        data-category="{{ $cat->slug }}">{{ $cat->name }}</button>
                @endforeach
            </div>
            @if($childCategories->count())
            <div class="mt-2 ps-2 border-start border-2">
                @foreach($childCategories as $child)
                <button type="button" class="shop-cat-pill category-pill {{ $activeCategory && $activeCategory->id === $child->id ? 'active' : '' }}"
                        data-category="{{ $child->slug }}">{{ $child->name }}</button>
                @endforeach
            </div>
            @endif
        </div>

        <div id="dynamic-filters">
            @include('frontend.products.partials.filters', [
                'styleFilters' => $styleFilters ?? [],
                'categoryAttributes' => $categoryAttributes ?? collect(),
                'activeStyle' => $activeStyle ?? 'all',
                'minPrice' => $minPrice ?? null,
                'maxPrice' => $maxPrice ?? null,
            ])
        </div>

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3 pt-3 border-top">
            <span class="shop-count" id="product-count">Showing <strong id="loaded-count">{{ $products->count() }}</strong> of <strong id="total-count">{{ $total }}</strong></span>
            <select class="form-select form-select-sm w-auto" id="sort-select">
                <option value="latest" {{ request('sort', 'latest') === 'latest' ? 'selected' : '' }}>Latest</option>
                <option value="price_low" {{ request('sort') === 'price_low' ? 'selected' : '' }}>Price: Low → High</option>
                <option value="price_high" {{ request('sort') === 'price_high' ? 'selected' : '' }}>Price: High → Low</option>
                <option value="name" {{ request('sort') === 'name' ? 'selected' : '' }}>Name A–Z</option>
            </select>
        </div>
    </div>

    <div class="row g-4" id="product-grid">
        @include('frontend.products.partials.grid-items', ['products' => $products])
    </div>

    <div id="grid-empty" class="alert alert-light text-center {{ $products->count() ? 'd-none' : '' }}">
        No products match your filters.
    </div>

    <div id="load-sentinel"></div>
    <div class="shop-loading d-none" id="load-spinner">
        <div class="spinner-border spinner-border-sm text-secondary" role="status"></div>
        <span class="ms-2">Loading more…</span>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function() {
    const grid = document.getElementById('product-grid');
    const sentinel = document.getElementById('load-sentinel');
    const spinner = document.getElementById('load-spinner');
    const emptyEl = document.getElementById('grid-empty');
    const dynamicFilters = document.getElementById('dynamic-filters');
    const loadedCountEl = document.getElementById('loaded-count');
    const totalCountEl = document.getElementById('total-count');

    let page = 1;
    let hasMore = @json($hasMore);
    let loading = false;
    let selectedCategory = @json($activeCategory?->slug ?? '');
    let selectedStyle = @json($activeStyle ?? 'all');
    const selectedAttrs = {};

    function buildParams(pageNum) {
        const p = new URLSearchParams();
        p.set('page', pageNum);
        if (selectedCategory) p.set('category', selectedCategory);
        if (selectedStyle && selectedStyle !== 'all') p.set('style', selectedStyle);
        const search = document.getElementById('shop-search')?.value.trim();
        if (search) p.set('search', search);
        const sort = document.getElementById('sort-select')?.value;
        if (sort) p.set('sort', sort);
        const minP = document.getElementById('filter-min-price')?.value;
        const maxP = document.getElementById('filter-max-price')?.value;
        if (minP) p.set('min_price', minP);
        if (maxP) p.set('max_price', maxP);
        Object.entries(selectedAttrs).forEach(([id, val]) => p.set('attr_' + id, val));
        return p;
    }

    function updateUrl() {
        const p = buildParams(1);
        p.delete('page');
        const qs = p.toString();
        history.replaceState(null, '', qs ? '?' + qs : '{{ route('products.index') }}');
    }

    async function refreshFilters() {
        const p = buildParams(1);
        p.delete('page');
        const res = await fetch('{{ route('products.filters') }}?' + p.toString(), {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await res.json();
        dynamicFilters.innerHTML = data.html;
        bindFilterPills();
        if (selectedStyle) {
            dynamicFilters.querySelectorAll('.style-pill').forEach(el => {
                el.classList.toggle('active', el.dataset.style === selectedStyle);
            });
        }
        Object.entries(selectedAttrs).forEach(([id, val]) => {
            dynamicFilters.querySelector(`.attr-pill[data-attr-id="${id}"][data-value-id="${val}"]`)?.classList.add('active');
        });
    }

    async function loadProducts(reset) {
        if (loading) return;
        if (!reset && !hasMore) return;
        loading = true;
        spinner.classList.remove('d-none');

        if (reset) {
            page = 1;
            hasMore = true;
            grid.innerHTML = '';
        }

        const p = buildParams(page);
        try {
            const res = await fetch('{{ route('products.load') }}?' + p.toString(), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await res.json();

            if (reset) {
                grid.innerHTML = data.html;
            } else {
                grid.insertAdjacentHTML('beforeend', data.html);
            }

            hasMore = data.has_more;
            loadedCountEl.textContent = data.loaded;
            totalCountEl.textContent = data.total;
            emptyEl.classList.toggle('d-none', data.loaded > 0);

            if (hasMore) page++;
            updateUrl();
        } finally {
            loading = false;
            spinner.classList.add('d-none');
        }
    }

    function resetAndLoad() {
        loadProducts(true);
    }

    function bindFilterPills() {
        dynamicFilters.querySelectorAll('.style-pill').forEach(btn => {
            btn.addEventListener('click', () => {
                selectedStyle = btn.dataset.style;
                dynamicFilters.querySelectorAll('.style-pill').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                resetAndLoad();
            });
        });
        dynamicFilters.querySelectorAll('.attr-pill').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.dataset.attrId;
                const val = btn.dataset.valueId;
                if (selectedAttrs[id] === val) {
                    delete selectedAttrs[id];
                    btn.classList.remove('active');
                } else {
                    dynamicFilters.querySelectorAll(`.attr-pill[data-attr-id="${id}"]`).forEach(b => b.classList.remove('active'));
                    selectedAttrs[id] = val;
                    btn.classList.add('active');
                }
                resetAndLoad();
            });
        });
        ['filter-min-price', 'filter-max-price'].forEach(id => {
            const el = document.getElementById(id);
            if (el && !el.dataset.bound) {
                el.dataset.bound = '1';
                el.addEventListener('change', () => resetAndLoad());
            }
        });
    }

    document.querySelectorAll('.category-pill').forEach(btn => {
        btn.addEventListener('click', async () => {
            selectedCategory = btn.dataset.category;
            selectedStyle = 'all';
            Object.keys(selectedAttrs).forEach(k => delete selectedAttrs[k]);
            document.querySelectorAll('.category-pill').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            await refreshFilters();
            resetAndLoad();
        });
    });

    document.getElementById('sort-select')?.addEventListener('change', resetAndLoad);
    document.getElementById('shop-search-form')?.addEventListener('submit', e => {
        e.preventDefault();
        resetAndLoad();
    });

    bindFilterPills();

    const observer = new IntersectionObserver(entries => {
        if (entries[0].isIntersecting && hasMore && !loading) {
            loadProducts(false);
        }
    }, { rootMargin: '200px' });
    observer.observe(sentinel);

    if (@json($products->count()) === 0) {
        hasMore = false;
    } else if (@json($hasMore)) {
        page = 2;
    }
})();
</script>
@endpush
