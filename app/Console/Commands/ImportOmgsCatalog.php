<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Shop;
use App\Services\OmgsCatalogService;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ImportOmgsCatalog extends Command
{
    protected $signature = 'omgs:import-catalog {--images : Download images from each OMGS product page} {--force : Replace existing OMGS products}';

    protected $description = 'Import OMGS.in categories, products, style filters and images';

    public function handle(OmgsCatalogService $omgs): int
    {
        $shop = Shop::where('is_active', true)->first();
        if (! $shop) {
            $this->error('No active shop found. Run vendor seeder first.');

            return self::FAILURE;
        }

        $withImages = $this->option('images');
        $force = $this->option('force');

        $parent = Category::updateOrCreate(
            ['slug' => 'custom-photo-decor'],
            ['name' => 'Custom Photo & Décor', 'parent_id' => null, 'sort_order' => 0, 'is_active' => true]
        );

        [$printSize, $thickness] = $omgs->ensureVariantAttributes();

        $totalProducts = 0;
        $totalImages = 0;

        foreach (OmgsCatalogService::CATALOG_LINES as $key => $line) {
            $this->info("Line: {$line['name']}");

            $category = Category::updateOrCreate(
                ['slug' => $line['slug']],
                [
                    'name' => $line['name'],
                    'parent_id' => $parent->id,
                    'hub_route_slug' => $line['hub'] ?? $line['slug'],
                    'is_active' => true,
                    'sort_order' => array_search($key, array_keys(OmgsCatalogService::CATALOG_LINES)) + 1,
                ]
            );

            $category->attributes()->syncWithoutDetaching([
                $printSize->id => ['is_required' => true, 'sort_order' => 1],
                $thickness->id => ['is_required' => true, 'sort_order' => 2],
            ]);

            if (! $category->image && $withImages) {
                $catImg = $omgs->scrapeProductImage($line['source']);
                if ($catImg && ($path = $omgs->downloadImage($catImg, 'categories', $category->slug))) {
                    $category->update(['image' => $path]);
                    $this->line("  Category image saved");
                }
            }

            $scraped = $omgs->scrapeProductNames($line['source']);
            $this->line('  Found ' . count($scraped) . ' products on OMGS');

            $sort = 0;
            foreach ($scraped as $item) {
                $name = $item['name'] ?? Str::title(str_replace('-', ' ', $item['slug']));
                $slug = Str::slug($item['slug']);
                $sourceUrl = $item['url'] ?? null;

                $basePrice = match ($line['slug']) {
                    'wall-clocks' => 1499,
                    'fridge-magnets' => 299,
                    'acrylic-photo-stand' => 499,
                    'keychains' => 249,
                    default => 899,
                };

                $product = Product::updateOrCreate(
                    ['slug' => $slug],
                    [
                        'name' => $name,
                        'short_description' => 'Personalised ' . $line['name'] . ' — upload your photo. Prices in ₹.',
                        'description' => $this->defaultDescription($name, $line['name']),
                        'category_id' => $category->id,
                        'shop_id' => $shop->id,
                        'price' => $basePrice,
                        'manage_stock' => false,
                        'in_stock' => true,
                        'is_active' => true,
                        'is_customizable' => true,
                        'is_featured' => $sort < 2,
                        'shape_label' => $omgs->detectShape($name),
                        'style_filter' => $omgs->detectStyleFilter($name),
                        'omgs_source_url' => $sourceUrl,
                        'allows_cod' => ! str_contains(strtolower($name), 'acrylic wall photo'),
                        'processing_days_min' => 1,
                        'processing_days_max' => 7,
                        'sort_order' => $sort++,
                    ]
                );

                $omgs->syncProductVariants($product, $basePrice);

                if ($withImages && $sourceUrl) {
                    $imgUrl = $omgs->scrapeProductImage($sourceUrl);
                    if ($imgUrl) {
                        if ($force) {
                            $product->images()->delete();
                        }
                        if (! $product->images()->exists()) {
                            if ($path = $omgs->downloadImage($imgUrl, 'products', $slug)) {
                                ProductImage::create([
                                    'product_id' => $product->id,
                                    'image_path' => $path,
                                    'is_primary' => true,
                                    'sort_order' => 0,
                                ]);
                                $totalImages++;
                            }
                        }
                    }
                    usleep(300000); // polite delay
                }

                $totalProducts++;
                $this->line("    + {$name}");
            }
        }

        $this->newLine();
        $this->info("Imported/updated {$totalProducts} customizable products. Images: {$totalImages}.");

        return self::SUCCESS;
    }

    private function defaultDescription(string $productName, string $lineName): string
    {
        return "{$productName} — part of our {$lineName} collection.\n\n"
            . "• Upload your high-resolution photo\n"
            . "• Choose size (inches) and acrylic thickness\n"
            . "• UV print on premium acrylic\n"
            . "• Ships across India (INR pricing)\n\n"
            . "Inspired by OMGS-style personalised décor.";
    }
}
