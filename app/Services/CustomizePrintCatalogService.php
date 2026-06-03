<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class CustomizePrintCatalogService
{
    public const SCHOOL_STICKER_CATEGORY = [
        'slug' => 'school-name-slip-sticker',
        'name' => 'School Name Slip Sticker',
        'hub' => 'school-name-slip-sticker',
        'source' => 'https://customizeprint.in/product-category/school-name-slip-sticker/',
    ];

    /**
     * @return array<int, array{slug: string, name: string, url: string, image_url?: string}>
     */
    public function scrapeCategoryProducts(string $url): array
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

            if (preg_match_all(
                '#<a[^>]+href="([^"]+)"[^>]*class="[^"]*woocommerce-LoopProduct-link[^"]*"[^>]*>.*?src="([^"]+)"[^>]*alt="([^"]*)".*?woocommerce-loop-product__title">([^<]+)</h2>#is',
                $html,
                $matches,
                PREG_SET_ORDER
            )) {
                foreach ($matches as $match) {
                    $this->addScrapedProduct($products, $match[1], $match[2], $match[4]);
                }
            }

            if (preg_match_all(
                '#class="[^"]*woocommerce-LoopProduct-link[^"]*"[^>]*href="([^"]+)"[^>]*>.*?src="([^"]+)".*?woocommerce-loop-product__title">([^<]+)</h2>#is',
                $html,
                $matches2,
                PREG_SET_ORDER
            )) {
                foreach ($matches2 as $match) {
                    $this->addScrapedProduct($products, $match[1], $match[2], $match[3]);
                }
            }

            return array_values($products);
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * @param  array<string, array{slug: string, name: string, url: string, image_url?: string}>  $products
     */
    private function addScrapedProduct(array &$products, string $url, string $image, string $title): void
    {
        $productUrl = html_entity_decode($url);
        if (! $this->isSchoolStickerUrl($productUrl)) {
            return;
        }
        $slug = $this->slugFromUrl($productUrl);
        $products[$slug] = [
            'slug' => $slug,
            'name' => trim(html_entity_decode($title)),
            'url' => $productUrl,
            'image_url' => html_entity_decode($image),
        ];
    }

    public function isSchoolStickerUrl(string $url): bool
    {
        $path = strtolower(parse_url($url, PHP_URL_PATH) ?? '');

        return str_contains($path, 'sticker')
            || str_contains($path, 'school-notebook')
            || str_contains($path, 'school-name');
    }

    public function scrapeProductDetails(string $productUrl): array
    {
        $defaults = [
            'price' => 199,
            'compare_price' => 299,
            'image_url' => null,
            'short_description' => null,
            'description' => null,
        ];

        try {
            $response = Http::timeout(45)
                ->withHeaders(['User-Agent' => 'Mozilla/5.0'])
                ->get($productUrl);

            if (! $response->successful()) {
                return $defaults;
            }

            $html = $response->body();

            if (preg_match('#<meta[^>]+property=["\']og:image["\'][^>]+content=["\']([^"\']+)["\']#i', $html, $m)) {
                $defaults['image_url'] = html_entity_decode($m[1]);
            }

            if (preg_match('#<meta[^>]+property=["\']og:description["\'][^>]+content=["\']([^"\']+)["\']#i', $html, $m)) {
                $defaults['short_description'] = html_entity_decode($m[1]);
            }

            if (preg_match_all('#class="woocommerce-Price-amount[^"]*"[^>]*>.*?([\d,]+(?:\.\d+)?)#is', $html, $prices)) {
                $amounts = array_map(fn ($p) => (float) str_replace(',', '', $p), $prices[1]);
                $amounts = array_values(array_filter($amounts, fn ($p) => $p > 0));
                if (count($amounts) >= 2) {
                    $defaults['compare_price'] = max($amounts);
                    $defaults['price'] = min($amounts);
                } elseif (count($amounts) === 1) {
                    $defaults['price'] = $amounts[0];
                }
            }

            if (preg_match('#woocommerce-product-details__short-description[^>]*>(.*?)</div>#is', $html, $m)) {
                $defaults['short_description'] = trim(strip_tags(html_entity_decode($m[1])));
            }

            if (preg_match('#id="tab-description"[^>]*>(.*?)</div>\s*</div>#is', $html, $m)) {
                $defaults['description'] = trim(strip_tags(html_entity_decode($m[1])));
            }

            return $defaults;
        } catch (\Throwable) {
            return $defaults;
        }
    }

    public function detectDesignCode(string $name, string $slug): string
    {
        if (preg_match('/d[-\s]?(\d{1,2})/i', $name . ' ' . $slug, $m)) {
            return 'design-' . str_pad($m[1], 2, '0', STR_PAD_LEFT);
        }

        return 'design';
    }

    public function defaultStickerDescription(string $name): string
    {
        return "{$name}\n\n"
            . "• Set of 30 personalised notebook name stickers\n"
            . "• Waterproof, peel-and-stick labels for school books & copies\n"
            . "• Enter student name, class, and contact details at checkout\n"
            . "• Printed in high quality — ships across India\n"
            . "• Estimated delivery: 3–5 working days\n\n"
            . "Perfect for school notebooks, textbooks, and stationery.";
    }

    public function slugFromUrl(string $url): string
    {
        $path = parse_url($url, PHP_URL_PATH) ?? $url;

        return trim(basename(rtrim($path, '/')), '/');
    }

    public function downloadImage(string $url, string $folder, string $slug): ?string
    {
        try {
            $response = Http::timeout(90)->withHeaders(['User-Agent' => 'Mozilla/5.0'])->get($url);
            if (! $response->successful() || strlen($response->body()) < 500) {
                return null;
            }

            $ext = str_contains($response->header('Content-Type') ?? '', 'png') ? 'png' : 'jpg';
            $path = $folder . '/' . Str::slug($slug) . '.' . $ext;
            \Illuminate\Support\Facades\Storage::disk('public')->put($path, $response->body());

            return $path;
        } catch (\Throwable) {
            return null;
        }
    }
}
