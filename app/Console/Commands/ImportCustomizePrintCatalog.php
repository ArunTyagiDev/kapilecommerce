<?php

namespace App\Console\Commands;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\Shop;
use App\Services\CustomizePrintCatalogService;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ImportCustomizePrintCatalog extends Command
{
    protected $signature = 'customizeprint:import-school-stickers
                            {--images : Download product images}
                            {--force : Replace images on existing products}
                            {--limit=12 : Max products to import (0 = all)}';

    protected $description = 'Import school name slip stickers from customizeprint.in';

    public function handle(CustomizePrintCatalogService $cp): int
    {
        $shop = Shop::where('is_active', true)->first();
        if (! $shop) {
            $this->error('No active shop. Run vendor seeder first.');

            return self::FAILURE;
        }

        $line = CustomizePrintCatalogService::SCHOOL_STICKER_CATEGORY;
        $withImages = $this->option('images');
        $force = $this->option('force');
        $limit = (int) $this->option('limit');

        $parent = Category::updateOrCreate(
            ['slug' => 'custom-photo-decor'],
            ['name' => 'Custom Photo & Décor', 'parent_id' => null, 'sort_order' => 0, 'is_active' => true]
        );

        $category = Category::updateOrCreate(
            ['slug' => $line['slug']],
            [
                'name' => $line['name'],
                'parent_id' => $parent->id,
                'hub_route_slug' => $line['hub'],
                'description' => 'Personalised school notebook name label stickers — 30 pcs per pack. Choose a design and enter student details.',
                'is_active' => true,
                'sort_order' => 50,
            ]
        );

        $packAttr = $this->ensurePackAttribute();
        $category->attributes()->syncWithoutDetaching([
            $packAttr->id => ['is_required' => true, 'sort_order' => 1],
        ]);

        $scraped = $cp->scrapeCategoryProducts($line['source']);
        $this->info('Found ' . count($scraped) . ' sticker designs on customizeprint.in');

        if ($limit > 0) {
            $scraped = array_slice($scraped, 0, $limit);
            $this->line("Importing first {$limit} designs (--limit=0 for all)");
        }

        $packValueId = AttributeValue::where('attribute_id', $packAttr->id)->value('id');
        $imported = 0;
        $images = 0;

        foreach ($scraped as $sort => $item) {
            $name = $item['name'] ?: Str::title(str_replace('-', ' ', $item['slug']));
            $slug = Str::slug($item['slug']);
            $sourceUrl = $item['url'];

            $details = $cp->scrapeProductDetails($sourceUrl);
            $imgUrl = $details['image_url'] ?? $item['image_url'] ?? null;

            $description = $details['description'] ?: $cp->defaultStickerDescription($name);
            $short = $details['short_description'] ?: '30 pcs school notebook name stickers — personalised with student details.';

            $product = Product::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $name,
                    'short_description' => $short,
                    'description' => $description,
                    'category_id' => $category->id,
                    'shop_id' => $shop->id,
                    'price' => $details['compare_price'],
                    'discount_price' => $details['price'],
                    'manage_stock' => false,
                    'in_stock' => true,
                    'is_active' => true,
                    'is_customizable' => true,
                    'customization_type' => 'text_sticker',
                    'is_featured' => $sort < 4,
                    'shape_label' => 'Sticker sheet',
                    'style_filter' => $cp->detectDesignCode($name, $slug),
                    'omgs_source_url' => $sourceUrl,
                    'allows_cod' => true,
                    'processing_days_min' => 2,
                    'processing_days_max' => 5,
                    'sort_order' => $sort,
                ]
            );

            ProductVariant::updateOrCreate(
                [
                    'product_id' => $product->id,
                    'sku' => $slug . '-30pcs',
                ],
                [
                    'price' => $details['compare_price'],
                    'discount_price' => $details['price'],
                    'stock_quantity' => 99,
                    'in_stock' => true,
                ]
            )->attributes()->sync([$packAttr->id => ['attribute_value_id' => $packValueId]]);

            if ($withImages && $imgUrl) {
                if ($force) {
                    $product->images()->delete();
                }
                if (! $product->images()->exists()) {
                    if ($path = $cp->downloadImage($imgUrl, 'products', $slug)) {
                        ProductImage::create([
                            'product_id' => $product->id,
                            'image_path' => $path,
                            'is_primary' => true,
                            'sort_order' => 0,
                        ]);
                        $images++;
                    }
                }
                usleep(250000);
            }

            $imported++;
            $this->line("  + {$name}");
        }

        $this->newLine();
        $this->info("Imported {$imported} school sticker products. Images: {$images}.");
        $this->line('Hub: /customise/school-name-slip-sticker');

        return self::SUCCESS;
    }

    private function ensurePackAttribute(): Attribute
    {
        $attr = Attribute::firstOrCreate(
            ['slug' => 'pack-size'],
            ['name' => 'Pack size', 'type' => 'select', 'is_filterable' => false]
        );
        AttributeValue::firstOrCreate(
            ['attribute_id' => $attr->id, 'value' => '30 pcs'],
            ['display_value' => '30 stickers per pack', 'sort_order' => 1]
        );

        return $attr;
    }
}
