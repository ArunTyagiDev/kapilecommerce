<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Shop;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CustomPhotoSeeder extends Seeder
{
    public function run(): void
    {
        $shop = Shop::where('slug', 'kitchencraft-store')->first()
            ?? Shop::first();

        if (! $shop) {
            return;
        }

        $parent = Category::updateOrCreate(
            ['slug' => 'custom-photo-decor'],
            ['name' => 'Custom Photo & Décor', 'parent_id' => null, 'sort_order' => 0, 'is_active' => true]
        );

        $lines = [
            'acrylic-wall-photo' => 'Acrylic Wall Photo',
            'wall-clocks' => 'Acrylic Wall Clocks',
            'framed-acrylic' => 'Framed Acrylic Photo',
            'fridge-magnets' => 'Acrylic Fridge Magnets',
            'keychains' => 'Personalised Keychains',
            'nameplates' => 'Acrylic Nameplates',
        ];

        $lineCategories = [];
        $order = 1;
        foreach ($lines as $slug => $name) {
            $lineCategories[$slug] = Category::updateOrCreate(
                ['slug' => $slug],
                ['name' => $name, 'parent_id' => $parent->id, 'sort_order' => $order++, 'is_active' => true]
            );
        }

        $printSize = Attribute::updateOrCreate(
            ['slug' => 'print-size'],
            ['name' => 'Print Size (inches)', 'type' => 'select', 'is_filterable' => true]
        );

        $sizes = ['8×6', '12×9', '9×12', '11×11', '12×16', '16×12', '18×12', '21×15'];
        foreach ($sizes as $i => $size) {
            AttributeValue::updateOrCreate(
                ['attribute_id' => $printSize->id, 'value' => $size],
                ['display_value' => $size . ' in', 'sort_order' => $i + 1]
            );
        }

        $thickness = Attribute::updateOrCreate(
            ['slug' => 'thickness'],
            ['name' => 'Acrylic Thickness', 'type' => 'select', 'is_filterable' => true]
        );

        foreach (['3mm', '8mm'] as $i => $t) {
            AttributeValue::updateOrCreate(
                ['attribute_id' => $thickness->id, 'value' => $t],
                ['display_value' => $t, 'sort_order' => $i + 1]
            );
        }

        foreach ($lineCategories as $cat) {
            $cat->attributes()->sync([
                $printSize->id => ['is_required' => true, 'sort_order' => 1],
                $thickness->id => ['is_required' => true, 'sort_order' => 2],
            ]);
        }

        $acrylicStyles = [
            ['name' => 'Portrait Acrylic Wall Photo', 'shape' => 'Portrait', 'base' => 899],
            ['name' => 'Landscape Acrylic Wall Photo', 'shape' => 'Landscape', 'base' => 899],
            ['name' => 'Square Acrylic Wall Photo', 'shape' => 'Square', 'base' => 999],
            ['name' => 'Circle Acrylic Wall Photo', 'shape' => 'Circle', 'base' => 1099],
            ['name' => 'Portrait Dual Border Acrylic Photo', 'shape' => 'Portrait', 'base' => 1299],
            ['name' => 'Landscape Dual Border Acrylic Photo', 'shape' => 'Landscape', 'base' => 1299],
            ['name' => 'Rounded Rectangle Portrait Photo', 'shape' => 'Portrait', 'base' => 1199],
            ['name' => 'Balloon Shape Acrylic Wall Photo', 'shape' => 'Square', 'base' => 1399],
        ];

        $cat = $lineCategories['acrylic-wall-photo'];
        $this->seedCustomProducts($shop, $cat, $acrylicStyles, $printSize, $thickness);

        $clockStyles = [
            ['name' => 'Round Acrylic Wall Clock', 'shape' => 'Circle', 'base' => 1499],
            ['name' => 'Square Acrylic Desk Clock', 'shape' => 'Square', 'base' => 1299],
        ];
        $this->seedCustomProducts($shop, $lineCategories['wall-clocks'], $clockStyles, $printSize, $thickness);

        $framedStyles = [
            ['name' => 'Framed Portrait Acrylic Photo', 'shape' => 'Portrait', 'base' => 1599],
            ['name' => 'Aluminium Framed Acrylic Photo', 'shape' => 'Landscape', 'base' => 2199],
        ];
        $this->seedCustomProducts($shop, $lineCategories['framed-acrylic'], $framedStyles, $printSize, $thickness);

        $smallStyles = [
            ['name' => 'Acrylic Photo Fridge Magnet Set', 'shape' => 'Square', 'base' => 399],
            ['name' => 'Personalised Photo Keychain', 'shape' => 'Square', 'base' => 299],
            ['name' => 'Acrylic Home Nameplate', 'shape' => 'Landscape', 'base' => 899],
        ];
        $this->seedCustomProducts($shop, $lineCategories['fridge-magnets'], [$smallStyles[0]], $printSize, $thickness);
        $this->seedCustomProducts($shop, $lineCategories['keychains'], [$smallStyles[1]], $printSize, $thickness, allowsCod: true);
        $this->seedCustomProducts($shop, $lineCategories['nameplates'], [$smallStyles[2]], $printSize, $thickness, allowsCod: false);
    }

    private function seedCustomProducts(
        Shop $shop,
        Category $category,
        array $styles,
        Attribute $printSize,
        Attribute $thickness,
        bool $allowsCod = false
    ): void {
        $sizeIds = AttributeValue::where('attribute_id', $printSize->id)->pluck('id', 'value');
        $thickIds = AttributeValue::where('attribute_id', $thickness->id)->pluck('id', 'value');

        foreach ($styles as $i => $style) {
            $slug = Str::slug($style['name']);
            $product = Product::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $style['name'],
                    'short_description' => 'Upload your photo — UV printed premium acrylic. Prices in INR.',
                    'description' => "Custom {$style['name']}.\n\n• Upload high-resolution photo\n• Choose size in inches & acrylic thickness (3mm / 8mm)\n• Processing 1–3 days, delivery 3–7 days across India\n• Ultra HD UV print on imported acrylic\n\nNote: Use clear, non-blurry photos. CMYK print — slight colour variation vs screen.",
                    'category_id' => $category->id,
                    'shop_id' => $shop->id,
                    'price' => $style['base'],
                    'discount_price' => null,
                    'stock_quantity' => 0,
                    'manage_stock' => false,
                    'in_stock' => true,
                    'is_active' => true,
                    'is_featured' => $i < 2,
                    'is_customizable' => true,
                    'shape_label' => $style['shape'],
                    'allows_cod' => $allowsCod,
                    'processing_days_min' => 1,
                    'processing_days_max' => 7,
                    'sort_order' => $i + 1,
                ]
            );

            $product->variants()->delete();

            foreach ($sizeIds as $sizeValue => $sizeId) {
                foreach ($thickIds as $thickValue => $thickId) {
                    $extra = ($thickValue === '8mm' ? 400 : 0) + (strlen($sizeValue) > 4 ? 200 : 0);
                    $price = $style['base'] + $extra;

                    $variant = ProductVariant::create([
                        'product_id' => $product->id,
                        'price' => $price,
                        'stock_quantity' => 50,
                        'in_stock' => true,
                    ]);

                    $variant->attributes()->attach($printSize->id, ['attribute_value_id' => $sizeId]);
                    $variant->attributes()->attach($thickness->id, ['attribute_value_id' => $thickId]);
                }
            }
        }
    }
}
