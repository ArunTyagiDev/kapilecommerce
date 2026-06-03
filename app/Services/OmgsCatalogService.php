<?php

namespace App\Services;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\Shop;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class OmgsCatalogService
{
    /** @var array<string, array{slug: string, name: string, hub?: string, source: string, type: string}> */
    public const CATALOG_LINES = [
        'acrylic-print' => [
            'slug' => 'acrylic-wall-photo',
            'name' => 'Acrylic Wall Photo',
            'hub' => 'acrylic-print',
            'source' => 'https://omgs.in/customise/acrylic-print',
            'type' => 'customise',
        ],
        'wall-clocks' => [
            'slug' => 'wall-clocks',
            'name' => 'Wall Clocks',
            'hub' => 'wall-clocks',
            'source' => 'https://omgs.in/customise/wall-clocks',
            'type' => 'customise',
        ],
        'fridge-magnets' => [
            'slug' => 'fridge-magnets',
            'name' => 'Acrylic Photo Fridge Magnets',
            'hub' => 'fridge-magnets',
            'source' => 'https://omgs.in/product-category/acrylic-photo-fridge-magnets',
            'type' => 'category',
        ],
        'photo-stand' => [
            'slug' => 'acrylic-photo-stand',
            'name' => 'Acrylic Photo Stand',
            'hub' => 'acrylic-photo-stand',
            'source' => 'https://omgs.in/product-category/acrylic-photo-stand',
            'type' => 'category',
        ],
        'collage-acrylic' => [
            'slug' => 'collage-acrylic',
            'name' => 'Collage Acrylic Photo',
            'hub' => 'collage-acrylic-wall-photo',
            'source' => 'https://omgs.in/customise/collage-acrylic-wall-photo',
            'type' => 'customise',
        ],
        'framed-acrylic' => [
            'slug' => 'framed-acrylic',
            'name' => 'Aluminium Framed Acrylic Photo',
            'hub' => 'aluminium-framed-acrylic-photo',
            'source' => 'https://omgs.in/product-category/aluminium-framed-acrylic-photo',
            'type' => 'category',
        ],
        'keychains' => [
            'slug' => 'keychains',
            'name' => 'Personalised Keychains',
            'hub' => 'keychains',
            'source' => 'https://omgs.in/product-category/keychains',
            'type' => 'category',
        ],
        'nameplates' => [
            'slug' => 'nameplates',
            'name' => 'Acrylic Nameplates',
            'hub' => 'acrylic-name-plate',
            'source' => 'https://omgs.in/customise/acrylic-name-plate',
            'type' => 'customise',
        ],
        'mini-gallery' => [
            'slug' => 'acrylic-mini-gallery',
            'name' => 'Acrylic Photo Mini Wall Gallery',
            'hub' => 'mini-gallery',
            'source' => 'https://omgs.in/product-category/acrylic-photo-mini-wall-gallery',
            'type' => 'category',
        ],
        'monogram' => [
            'slug' => 'acrylic-monogram',
            'name' => 'Acrylic Monogram Nameplate',
            'hub' => 'acrylic-monogram-nameplate',
            'source' => 'https://omgs.in/customise/acrylic-monogram-nameplate',
            'type' => 'customise',
        ],
        'luggage-tags' => [
            'slug' => 'luggage-tags',
            'name' => 'Luggage Tags',
            'hub' => 'luggage-tags',
            'source' => 'https://omgs.in/product-category/luggage-tags',
            'type' => 'category',
        ],
        'photo-albums' => [
            'slug' => 'photo-albums',
            'name' => 'Photo Albums',
            'hub' => 'photo-albums',
            'source' => 'https://omgs.in/product-category/photo-albums',
            'type' => 'category',
        ],
    ];

    public const STYLE_FILTERS = [
        'all' => 'All',
        'collage' => 'Collage',
        'portrait' => 'Portrait',
        'square' => 'Square',
        'landscape' => 'Landscape',
        'dual-border' => 'Dual Border',
        'couple' => 'Couple',
        'baby-birth' => 'Baby Birth',
        'creative-wall' => 'Creative Wall',
        'circle' => 'Circle',
        'heart' => 'Heart',
        'leaf' => 'Leaf',
        'special' => 'Special Shape',
    ];

    public function detectStyleFilter(string $name): string
    {
        $n = strtolower($name);

        if (str_contains($n, 'collage') || preg_match('/\d+\s*photo/i', $name)) {
            return 'collage';
        }
        if (str_contains($n, 'couple') || str_contains($n, '2 photo') || str_contains($n, '2 pics')) {
            return 'couple';
        }
        if (str_contains($n, 'baby') || str_contains($n, 'birth')) {
            return 'baby-birth';
        }
        if (str_contains($n, 'dual border')) {
            return 'dual-border';
        }
        if (str_contains($n, 'landscape')) {
            return 'landscape';
        }
        if (str_contains($n, 'portrait')) {
            return 'portrait';
        }
        if (str_contains($n, 'square') || str_contains($n, 'squircle')) {
            return 'square';
        }
        if (str_contains($n, 'circle') || str_contains($n, 'round')) {
            return 'circle';
        }
        if (str_contains($n, 'heart')) {
            return 'heart';
        }
        if (str_contains($n, 'leaf') || str_contains($n, 'balloon') || str_contains($n, 'floral') || str_contains($n, 'triangle') || str_contains($n, 'curved') || str_contains($n, 'symmetrical')) {
            return 'special';
        }
        if (str_contains($n, 'creative')) {
            return 'creative-wall';
        }

        return 'portrait';
    }

    public function detectShape(string $name): string
    {
        $n = strtolower($name);
        if (str_contains($n, 'circle') || str_contains($n, 'round')) {
            return 'Circle';
        }
        if (str_contains($n, 'heart')) {
            return 'Heart';
        }
        if (str_contains($n, 'landscape')) {
            return 'Landscape';
        }
        if (str_contains($n, 'square')) {
            return 'Square';
        }

        return 'Portrait';
    }

    public function scrapeProductNames(string $url): array
    {
        try {
            $response = Http::timeout(60)
                ->withHeaders(['User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'])
                ->get($url);

            if (! $response->successful()) {
                return [];
            }

            $html = $response->body();
            $products = [];

            $this->collectProductLinks($html, $products);

            // Next.js SEO nav (product-category pages load client-side but embed links here)
            if (preg_match('#aria-label=["\']Products in category["\'][^>]*>.*?<ul>(.*?)</ul>#is', $html, $nav)) {
                $this->collectProductLinks($nav[1], $products);
            }

            return array_values($products);
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * @param  array<string, array{slug: string, name?: string, url: string}>  $products
     */
    private function collectProductLinks(string $html, array &$products): void
    {
        if (preg_match_all('#<li[^>]*>\s*<a[^>]+href=["\'](?:https://omgs\.in)?(/(?:custom|product)/([a-z0-9\-]+))["\'][^>]*>([^<]+)</a>#i', $html, $m, PREG_SET_ORDER)) {
            foreach ($m as $match) {
                $slug = $match[2];
                $products[$slug] = [
                    'slug' => $slug,
                    'name' => trim(html_entity_decode($match[3])),
                    'url' => 'https://omgs.in' . $match[1],
                ];
            }
        }

        if (preg_match_all('#href=["\'](?:https://omgs\.in)?(/(?:custom|product)/([a-z0-9\-]+))["\']#i', $html, $m2, PREG_SET_ORDER)) {
            foreach ($m2 as $match) {
                $slug = $match[2];
                if (! isset($products[$slug])) {
                    $products[$slug] = ['slug' => $slug, 'url' => 'https://omgs.in' . $match[1]];
                }
            }
        }

        if (preg_match_all('#<h2[^>]*class="[^"]*woocommerce-loop-product__title[^"]*"[^>]*>\s*<a[^>]+href=["\']([^"\']+)["\'][^>]*>([^<]+)</a>#i', $html, $m3, PREG_SET_ORDER)) {
            foreach ($m3 as $match) {
                $slug = $this->slugFromUrl($match[1]);
                $products[$slug] = ['slug' => $slug, 'name' => trim($match[2]), 'url' => $match[1]];
            }
        }
    }

    public function scrapeProductImage(string $productUrl): ?string
    {
        try {
            $response = Http::timeout(45)
                ->withHeaders(['User-Agent' => 'Mozilla/5.0'])
                ->get($productUrl);

            if (! $response->successful()) {
                return null;
            }

            $html = $response->body();

            if (preg_match('#<meta[^>]+property=["\']og:image["\'][^>]+content=["\']([^"\']+)["\']#i', $html, $m)) {
                return $m[1];
            }
            if (preg_match('#property=["\']og:image["\'][^>]+content=["\']([^"\']+)["\']#i', $html, $m)) {
                return $m[1];
            }

            preg_match_all('#(?:https?:)?//(?:s\.)?omgs\.in[^\s"\']+\.(?:jpg|jpeg|png|webp)(?:\?[^\s"\']*)?#i', $html, $imgs);
            $urls = array_unique($imgs[0] ?? []);
            foreach ($urls as $url) {
                $url = str_starts_with($url, '//') ? 'https:' . $url : $url;
                if (! str_contains(strtolower($url), 'logo') && ! str_contains($url, 'icon')) {
                    return $url;
                }
            }

            return null;
        } catch (\Throwable) {
            return null;
        }
    }

    public function downloadImage(string $url, string $folder, string $slug): ?string
    {
        try {
            $response = Http::timeout(90)->withHeaders(['User-Agent' => 'Mozilla/5.0'])->get($url);
            if (! $response->successful() || strlen($response->body()) < 800) {
                return null;
            }

            $ext = str_contains($response->header('Content-Type') ?? '', 'png') ? 'png' : 'jpg';
            $path = $folder . '/' . Str::slug($slug) . '.' . $ext;
            Storage::disk('public')->put($path, $response->body());

            return $path;
        } catch (\Throwable) {
            return null;
        }
    }

    public function slugFromUrl(string $url): string
    {
        $path = parse_url($url, PHP_URL_PATH) ?? $url;

        return trim(basename($path), '/');
    }

    public function ensureVariantAttributes(): array
    {
        $printSize = Attribute::firstOrCreate(
            ['slug' => 'print-size'],
            ['name' => 'Print Size (inches)', 'type' => 'select', 'is_filterable' => true]
        );
        foreach (['8×6', '12×9', '11×11', '12×16', '18×12'] as $i => $size) {
            AttributeValue::firstOrCreate(
                ['attribute_id' => $printSize->id, 'value' => $size],
                ['display_value' => $size, 'sort_order' => $i + 1]
            );
        }

        $thickness = Attribute::firstOrCreate(
            ['slug' => 'thickness'],
            ['name' => 'Acrylic Thickness', 'type' => 'select', 'is_filterable' => true]
        );
        foreach (['3mm', '8mm'] as $i => $t) {
            AttributeValue::firstOrCreate(
                ['attribute_id' => $thickness->id, 'value' => $t],
                ['display_value' => $t, 'sort_order' => $i + 1]
            );
        }

        return [$printSize, $thickness];
    }

    public function syncProductVariants(Product $product, int $basePrice): void
    {
        [$printSize, $thickness] = $this->ensureVariantAttributes();
        $sizeIds = AttributeValue::where('attribute_id', $printSize->id)->pluck('id', 'value');
        $thickIds = AttributeValue::where('attribute_id', $thickness->id)->pluck('id', 'value');

        $product->variants()->delete();

        foreach ($sizeIds as $size => $sizeId) {
            foreach ($thickIds as $thick => $thickId) {
                $extra = ($thick === '8mm' ? 350 : 0) + (strlen($size) > 4 ? 150 : 0);
                $variant = ProductVariant::create([
                    'product_id' => $product->id,
                    'price' => $basePrice + $extra,
                    'stock_quantity' => 99,
                    'in_stock' => true,
                ]);
                $variant->attributes()->attach($printSize->id, ['attribute_value_id' => $sizeId]);
                $variant->attributes()->attach($thickness->id, ['attribute_value_id' => $thickId]);
            }
        }
    }
}
