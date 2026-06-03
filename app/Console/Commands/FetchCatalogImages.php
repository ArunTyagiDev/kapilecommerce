<?php

namespace App\Console\Commands;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FetchCatalogImages extends Command
{
    protected $signature = 'catalog:fetch-images
                            {--force : Re-download even if image exists}
                            {--category= : Comma-separated category slugs (e.g. shoes,crockery)}';

    protected $description = 'Download category & product images (OMGS + themed stock) into storage';

    /** @var array<string, string> */
    private array $categoryUrls = [
        'shoes' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=800&q=80',
        'clothes' => 'https://images.unsplash.com/photo-1489987707025-afc232f7ea0f?w=800&q=80',
        'electronics' => 'https://images.unsplash.com/photo-1498049794561-7780e7231661?w=800&q=80',
        'crockery' => 'https://images.unsplash.com/photo-1556911220-e15b29be8c8f?w=800&q=80',
        'custom-photo-decor' => 'https://s.omgs.in/wp-content/uploads/2021/12/premium-look-acrylic-photo-frame.jpg',
        'men-shoes' => 'https://images.unsplash.com/photo-1606107557195-0a37455a247c?w=800&q=80',
        'women-shoes' => 'https://images.unsplash.com/photo-1543163521-1bf539c55dd2?w=800&q=80',
        'kids-shoes' => 'https://images.unsplash.com/photo-1515347619252-60b9877f3638?w=800&q=80',
        'running-shoes' => 'https://images.unsplash.com/photo-1605348533917-676ae8b33e44?w=800&q=80',
        'jeans' => 'https://images.unsplash.com/photo-1542272604-787c3835535d?w=800&q=80',
        'shirts' => 'https://images.unsplash.com/photo-1596755094514-f87e34085b2c?w=800&q=80',
        't-shirts' => 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=800&q=80',
        'kids-clothing' => 'https://images.unsplash.com/photo-1503454537845-cef8b2bfad26?w=800&q=80',
        'mobiles' => 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=800&q=80',
        'laptops' => 'https://images.unsplash.com/photo-1496181133206-80ce9b88a853?w=800&q=80',
        'accessories' => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=800&q=80',
        'dinner-sets' => 'https://images.unsplash.com/photo-1603199506016-b582d6f69133?w=800&q=80',
        'cookware' => 'https://images.unsplash.com/photo-1584990346859-02e0eecb0276?w=800&q=80',
        'serveware' => 'https://images.unsplash.com/photo-1578749556568-bc903401862c?w=800&q=80',
        'acrylic-wall-photo' => 'https://omgs.in/wp-content/uploads/2023/05/acrylic-wall-photo-square.jpg',
        'wall-clocks' => 'https://images.unsplash.com/photo-1563861829038-2409f5f5c8c0?w=800&q=80',
        'framed-acrylic' => 'https://s.omgs.in/wp-content/uploads/2021/12/premium-look-acrylic-photo-frame.jpg',
        'fridge-magnets' => 'https://images.unsplash.com/photo-1610701596007-11502861dcfa?w=800&q=80',
        'keychains' => 'https://images.unsplash.com/photo-1606760227091-3dd870edf709?w=800&q=80',
        'nameplates' => 'https://images.unsplash.com/photo-1618220179428-22790b461013?w=800&q=80',
    ];

    /** OMGS / décor product images by slug */
    private array $customProductUrls = [
        'portrait-acrylic-wall-photo' => 'https://omgs.in/wp-content/uploads/2023/05/acrylic-wall-photo-square.jpg',
        'landscape-acrylic-wall-photo' => 'https://s.omgs.in/wp-content/uploads/2021/02/mastersize-acrylic-photo.jpg',
        'square-acrylic-wall-photo' => 'https://omgs.in/wp-content/uploads/2023/05/acrylic-wall-photo-square.jpg',
        'circle-acrylic-wall-photo' => 'https://s.omgs.in/wp-content/uploads/2022/01/acrylic-wall-photo-reviews.jpg',
        'portrait-dual-border-acrylic-photo' => 'https://s.omgs.in/wp-content/uploads/2021/12/premium-look-acrylic-photo-frame.jpg',
        'landscape-dual-border-acrylic-photo' => 'https://omgs.in/wp-content/uploads/2023/05/OMGS-wall-acryllic-min.jpg',
        'rounded-rectangle-portrait-photo' => 'https://s.omgs.in/wp-content/uploads/2021/02/wall-photo-picture-requirement.jpg',
        'balloon-shape-acrylic-wall-photo' => 'https://s.omgs.in/wp-content/uploads/2021/02/pexels-%E7%A5%9D-%E9%B9%A4%E6%A8%9E-683929-1024x683.jpg',
        'round-acrylic-wall-clock' => 'https://images.unsplash.com/photo-1563861829038-2409f5f5c8c0?w=800&q=80',
        'square-acrylic-desk-clock' => 'https://images.unsplash.com/photo-1526170375885-4d8ecf77b99f?w=800&q=80',
        'framed-portrait-acrylic-photo' => 'https://s.omgs.in/wp-content/uploads/2021/12/premium-look-acrylic-photo-frame.jpg',
        'aluminium-framed-acrylic-photo' => 'https://omgs.in/wp-content/uploads/2023/05/OMGS-wall-acryllic-min.jpg',
        'acrylic-photo-fridge-magnet-set' => 'https://images.unsplash.com/photo-1610701596007-11502861dcfa?w=800&q=80',
        'personalised-photo-keychain' => 'https://images.unsplash.com/photo-1606760227091-3dd870edf709?w=800&q=80',
        'acrylic-home-nameplate' => 'https://images.unsplash.com/photo-1618220179428-22790b461013?w=800&q=80',
    ];

    /** Shoes & crockery — unique product photo per slug */
    private array $retailProductUrls = [
        'kids-velcro-school-shoes' => 'https://images.unsplash.com/photo-1515347619252-60b9877f3638?w=900&h=900&fit=crop&q=85',
        'kids-sports-sneakers' => 'https://images.unsplash.com/photo-1608231387042-66d1773070a6?w=900&h=900&fit=crop&q=85',
        'kids-canvas-casual-shoes' => 'https://images.unsplash.com/photo-1560769629-975ec94e6a86?w=900&h=900&fit=crop&q=85',
        'men-leather-formal-shoes' => 'https://images.unsplash.com/photo-1614252239476-9d858759f4c2?w=900&h=900&fit=crop&q=85',
        'men-running-shoes-pro' => 'https://images.unsplash.com/photo-1606107557195-0a37455a247c?w=900&h=900&fit=crop&q=85',
        'women-block-heel-sandals' => 'https://images.unsplash.com/photo-1543163521-1bf539c55dd2?w=900&h=900&fit=crop&q=85',
        'women-comfort-walking-shoes' => 'https://images.unsplash.com/photo-1460353589841-81a2bb7fa0bb?w=900&h=900&fit=crop&q=85',
        'trail-grip-running-shoes' => 'https://images.unsplash.com/photo-1556906781-233a1f48c6b2?w=900&h=900&fit=crop&q=85',
        'ceramic-dinner-set-floral' => 'https://images.unsplash.com/photo-1603199506016-b582d6f69133?w=900&h=900&fit=crop&q=85',
        'premium-bone-china-dinner-set' => 'https://images.unsplash.com/photo-1610708887531-9e263aa8ae85?w=900&h=900&fit=crop&q=85',
        'non-stick-cookware-set-5pc' => 'https://images.unsplash.com/photo-1584990346859-02e0eecb0276?w=900&h=900&fit=crop&q=85',
        'stainless-steel-kadai-with-lid' => 'https://images.unsplash.com/photo-1585664421775-4e0882f82764?w=900&h=900&fit=crop&q=85',
        'serving-bowl-set-glass' => 'https://images.unsplash.com/photo-1578749556568-bc903401862c?w=900&h=900&fit=crop&q=85',
        'tea-cup-saucer-set' => 'https://images.unsplash.com/photo-1576092768241-decf8530c6c4?w=900&h=900&fit=crop&q=85',
    ];

    /** Pexels search queries for scraping fallback (slug => query) */
    private array $retailScrapeQueries = [
        'kids-velcro-school-shoes' => 'kids school shoes',
        'kids-sports-sneakers' => 'kids sneakers',
        'kids-canvas-casual-shoes' => 'kids canvas shoes',
        'men-leather-formal-shoes' => 'men leather formal shoes',
        'men-running-shoes-pro' => 'running shoes',
        'women-block-heel-sandals' => 'women heel sandals',
        'women-comfort-walking-shoes' => 'women walking shoes',
        'trail-grip-running-shoes' => 'trail running shoes',
        'ceramic-dinner-set-floral' => 'ceramic dinner set',
        'premium-bone-china-dinner-set' => 'bone china dinner set',
        'non-stick-cookware-set-5pc' => 'non stick cookware',
        'stainless-steel-kadai-with-lid' => 'stainless steel kadai',
        'serving-bowl-set-glass' => 'glass serving bowls',
        'tea-cup-saucer-set' => 'tea cup saucer set',
    ];

    /** Standard catalog products (slug => unsplash) */
    private array $standardProductKeywords = [
        'shoe' => 'https://images.unsplash.com/photo-1606107557195-0a37455a247c?w=800&q=80',
        'sneaker' => 'https://images.unsplash.com/photo-1605348533917-676ae8b33e44?w=800&q=80',
        'boot' => 'https://images.unsplash.com/photo-1608252676317-0442081bfad9?w=800&q=80',
        'jean' => 'https://images.unsplash.com/photo-1542272604-787c3835535d?w=800&q=80',
        'shirt' => 'https://images.unsplash.com/photo-1596755094514-f87e34085b2c?w=800&q=80',
        't-shirt' => 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=800&q=80',
        'hoodie' => 'https://images.unsplash.com/photo-1556821840-3a63f95609a7?w=800&q=80',
        'polo' => 'https://images.unsplash.com/photo-1586363102862-96a903da3fc9?w=800&q=80',
        'chino' => 'https://images.unsplash.com/photo-1473966968600-fa801b279a0a?w=800&q=80',
        'track' => 'https://images.unsplash.com/photo-1503341504253-dff4815485f1?w=800&q=80',
        'smartphone' => 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=800&q=80',
        'laptop' => 'https://images.unsplash.com/photo-1496181133206-80ce9b88a853?w=800&q=80',
        'earbud' => 'https://images.unsplash.com/photo-1590658268037-6bf12165a8df?w=800&q=80',
        'watch' => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=800&q=80',
        'speaker' => 'https://images.unsplash.com/photo-1608043152269-423dbba4e7e1?w=800&q=80',
        'phone' => 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=800&q=80',
        'dinner' => 'https://images.unsplash.com/photo-1603199506016-b582d6f69133?w=800&q=80',
        'cookware' => 'https://images.unsplash.com/photo-1584990346859-02e0eecb0276?w=800&q=80',
        'kadai' => 'https://images.unsplash.com/photo-1584990346859-02e0eecb0276?w=800&q=80',
        'bowl' => 'https://images.unsplash.com/photo-1578749556568-bc903401862c?w=800&q=80',
        'tea' => 'https://images.unsplash.com/photo-1576092768241-decf8530c6c4?w=800&q=80',
        'heel' => 'https://images.unsplash.com/photo-1543163521-1bf539c55dd2?w=800&q=80',
        'sandal' => 'https://images.unsplash.com/photo-1543163521-1bf539c55dd2?w=800&q=80',
        'velcro' => 'https://images.unsplash.com/photo-1515347619252-60b9877f3638?w=800&q=80',
        'canvas' => 'https://images.unsplash.com/photo-1515347619252-60b9877f3638?w=800&q=80',
        'leather' => 'https://images.unsplash.com/photo-1614252239476-9d858759f4c2?w=800&q=80',
        'formal' => 'https://images.unsplash.com/photo-1614252239476-9d858759f4c2?w=800&q=80',
        'floral' => 'https://images.unsplash.com/photo-1603199506016-b582d6f69133?w=800&q=80',
        'china' => 'https://images.unsplash.com/photo-1610708887531-9e263aa8ae85?w=800&q=80',
        'stainless' => 'https://images.unsplash.com/photo-1585664421775-4e0882f82764?w=800&q=80',
        'glass' => 'https://images.unsplash.com/photo-1578749556568-bc903401862c?w=800&q=80',
        'ceramic' => 'https://images.unsplash.com/photo-1603199506016-b582d6f69133?w=800&q=80',
        'trail' => 'https://images.unsplash.com/photo-1556906781-233a1f48c6b2?w=800&q=80',
        'comfort' => 'https://images.unsplash.com/photo-1460353589841-81a2bb7fa0bb?w=800&q=80',
    ];

    public function handle(): int
    {
        $force = $this->option('force');
        $categoryFilter = $this->parseCategoryFilter();

        $this->info('Scraping OMGS customise page for extra product thumbnails…');
        $scraped = $this->scrapeOmgsImages('https://omgs.in/customise/acrylic-print');
        if (count($scraped) > 0) {
            $this->line('  Found ' . count($scraped) . ' images on OMGS acrylic hub.');
        }

        Storage::disk('public')->makeDirectory('categories');
        Storage::disk('public')->makeDirectory('products');

        $catOk = 0;
        foreach (Category::with('parent')->get() as $category) {
            if ($categoryFilter && ! $this->categoryInFilter($category, $categoryFilter)) {
                continue;
            }
            if ($category->image && ! $force) {
                continue;
            }

            $url = $this->categoryUrls[$category->slug]
                ?? ($category->parent ? ($this->categoryUrls[$category->parent->slug] ?? null) : null)
                ?? $this->picsumUrl($category->slug);

            if ($path = $this->download($url, 'categories', $category->slug, true)) {
                $category->update(['image' => $path]);
                $catOk++;
                $this->line("  Category: {$category->name}");
            } else {
                $this->warn("  Skip category: {$category->name}");
            }
        }

        $prodOk = 0;
        $colorAttr = Attribute::where('slug', 'color')->first();
        $colorIds = $colorAttr
            ? AttributeValue::where('attribute_id', $colorAttr->id)->pluck('id')->all()
            : [];

        $productsQuery = Product::with('category.parent');
        if ($categoryFilter) {
            $productsQuery->whereIn('category_id', $categoryFilter['ids']);
        }

        foreach ($productsQuery->get() as $product) {
            if ($product->images()->exists() && ! $force) {
                continue;
            }

            $url = $this->resolveProductUrl($product, $scraped);

            if (isset($this->retailScrapeQueries[$product->slug]) && ! isset($this->retailProductUrls[$product->slug])) {
                $scrapedUrl = $this->scrapePexelsImage($this->retailScrapeQueries[$product->slug]);
                if ($scrapedUrl) {
                    $url = $scrapedUrl;
                    $this->line("  Scraped Pexels: {$product->name}");
                }
            }

            if ($path = $this->download($url, 'products', $product->slug, true)) {
                if ($force) {
                    $product->images()->delete();
                }

                $product->images()->create([
                    'image_path' => $path,
                    'is_primary' => true,
                    'sort_order' => 0,
                ]);

                // Color swatches for storefront (reuse primary for each color)
                foreach (array_slice($colorIds, 0, 3) as $i => $colorId) {
                    if (! $product->images()->where('attribute_value_id', $colorId)->exists()) {
                        $product->images()->create([
                            'image_path' => $path,
                            'attribute_value_id' => $colorId,
                            'is_primary' => false,
                            'sort_order' => $i + 1,
                        ]);
                    }
                }

                $prodOk++;
                $this->line("  Product: {$product->name}");
            }
        }

        $this->newLine();
        $this->info("Done. Categories updated: {$catOk}. Products with images: {$prodOk}.");

        if (! file_exists(public_path('storage'))) {
            $this->warn('Run: php artisan storage:link');
        }

        return self::SUCCESS;
    }

    private function scrapeOmgsImages(string $pageUrl): array
    {
        try {
            $response = Http::timeout(45)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                    'Accept' => 'text/html,application/xhtml+xml',
                ])
                ->get($pageUrl);

            if (! $response->successful()) {
                return [];
            }

            $html = $response->body();
            preg_match_all(
                '#(?:https?:)?//(?:s\.omgs\.in|omgs\.in)[^\s"\']+\.(?:jpg|jpeg|png|webp)(?:\?[^\s"\']*)?#i',
                $html,
                $matches
            );

            $urls = array_values(array_unique($matches[0] ?? []));
            $urls = array_map(fn ($u) => str_starts_with($u, '//') ? 'https:' . $u : $u, $urls);

            return array_values(array_filter($urls, fn ($u) => ! str_contains($u, 'logo') && ! str_contains($u, 'Logo')));
        } catch (\Throwable $e) {
            $this->warn('  OMGS scrape skipped: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * @return array{slugs: string[], ids: int[]}|null
     */
    private function parseCategoryFilter(): ?array
    {
        $input = $this->option('category');
        if (! $input) {
            return null;
        }

        $slugs = array_filter(array_map('trim', explode(',', $input)));
        $ids = [];

        foreach ($slugs as $slug) {
            $cat = Category::where('slug', $slug)->first();
            if (! $cat) {
                $this->warn("Category not found: {$slug}");

                continue;
            }
            $ids = array_merge($ids, $this->categoryTreeIds($cat->id));
        }

        return ['slugs' => $slugs, 'ids' => array_values(array_unique($ids))];
    }

    private function categoryTreeIds(int $rootId): array
    {
        $ids = [$rootId];
        $current = [$rootId];
        while (! empty($current)) {
            $children = Category::whereIn('parent_id', $current)->pluck('id')->all();
            $children = array_values(array_diff($children, $ids));
            if (empty($children)) {
                break;
            }
            $ids = array_merge($ids, $children);
            $current = $children;
        }

        return $ids;
    }

    private function categoryInFilter(Category $category, array $filter): bool
    {
        if (in_array($category->slug, $filter['slugs'], true)) {
            return true;
        }

        return in_array($category->id, $filter['ids'], true)
            || ($category->parent_id && in_array($category->parent_id, $filter['ids'], true));
    }

    private function scrapePexelsImage(string $query): ?string
    {
        try {
            $searchUrl = 'https://www.pexels.com/search/' . rawurlencode($query) . '/';
            $response = Http::timeout(45)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                    'Accept-Language' => 'en-US,en;q=0.9',
                ])
                ->get($searchUrl);

            if (! $response->successful()) {
                return null;
            }

            $html = $response->body();

            if (preg_match('#https://images\.pexels\.com/photos/\d+/[^"\']+\.(?:jpeg|jpg)\?[^"\']*#i', $html, $m)) {
                return html_entity_decode($m[0]);
            }

            if (preg_match('#property="og:image"[^>]+content="([^"]+)"#i', $html, $m)) {
                return html_entity_decode($m[1]);
            }
        } catch (\Throwable) {
            return null;
        }

        return null;
    }

    private function resolveProductUrl(Product $product, array $scraped): string
    {
        if (isset($this->retailProductUrls[$product->slug])) {
            return $this->retailProductUrls[$product->slug];
        }

        if (isset($this->customProductUrls[$product->slug])) {
            return $this->customProductUrls[$product->slug];
        }

        $name = strtolower($product->name);
        foreach ($this->standardProductKeywords as $keyword => $url) {
            if (str_contains($name, $keyword)) {
                return $url;
            }
        }

        if ($product->is_customizable && count($scraped) > 0) {
            $index = abs(crc32($product->slug)) % count($scraped);

            return $scraped[$index];
        }

        $catSlug = $product->category?->slug ?? 'product';

        return $this->categoryUrls[$catSlug]
            ?? $this->picsumUrl($product->slug);
    }

    private function picsumUrl(string $seed): string
    {
        return 'https://picsum.photos/seed/' . urlencode($seed) . '/800/600';
    }

    private function download(string $url, string $folder, string $slug, bool $allowFallback = true): ?string
    {
        $saved = $this->tryDownload($url, $folder, $slug);

        if ($saved) {
            return $saved;
        }

        if ($allowFallback && ! str_contains($url, 'placehold.co')) {
            $this->line("  Fallback image: {$slug}");
            return $this->tryDownload($this->placeholderUrl($slug), $folder, $slug);
        }

        return null;
    }

    private function placeholderUrl(string $slug): string
    {
        $label = urlencode(str_replace('-', ' ', $slug));

        return "https://placehold.co/800x600/2563eb/ffffff/jpg?text={$label}";
    }

    private function tryDownload(string $url, string $folder, string $slug): ?string
    {
        try {
            $response = Http::timeout(60)
                ->withOptions(['allow_redirects' => true])
                ->withHeaders(['User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'])
                ->get($url);

            if (! $response->successful()) {
                return null;
            }

            $body = $response->body();
            if (strlen($body) < 800) {
                return null;
            }

            $ext = 'jpg';
            $contentType = $response->header('Content-Type') ?? '';
            if (str_contains($contentType, 'png')) {
                $ext = 'png';
            } elseif (str_contains($contentType, 'webp')) {
                $ext = 'webp';
            }

            $filename = $folder . '/' . Str::slug($slug) . '.' . $ext;
            Storage::disk('public')->put($filename, $body);

            return $filename;
        } catch (\Throwable) {
            return null;
        }
    }
}
