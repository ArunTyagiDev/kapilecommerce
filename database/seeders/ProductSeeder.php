<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Shop;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $catalog = [
            'solemate-footwear' => [
                'kids-shoes' => [
                    ['name' => 'Kids Velcro School Shoes', 'price' => 1299, 'discount' => 1099, 'sizes' => ['kids-shoe-size', ['UK 8', 'UK 9', 'UK 10', 'UK 11']]],
                    ['name' => 'Kids Sports Sneakers', 'price' => 1599, 'sizes' => ['kids-shoe-size', ['UK 9', 'UK 10', 'UK 11', 'UK 12']]],
                    ['name' => 'Kids Canvas Casual Shoes', 'price' => 899, 'discount' => 749, 'sizes' => ['kids-shoe-size', ['UK 8', 'UK 9', 'UK 10']]],
                ],
                'men-shoes' => [
                    ['name' => 'Men Leather Formal Shoes', 'price' => 2499, 'discount' => 2199, 'sizes' => ['shoe-size', ['8', '9', '10', '11']]],
                    ['name' => 'Men Running Shoes Pro', 'price' => 3299, 'sizes' => ['shoe-size', ['7', '8', '9', '10', '11']]],
                ],
                'women-shoes' => [
                    ['name' => 'Women Block Heel Sandals', 'price' => 1899, 'discount' => 1599, 'sizes' => ['shoe-size', ['6', '7', '8', '9']]],
                    ['name' => 'Women Comfort Walking Shoes', 'price' => 2199, 'sizes' => ['shoe-size', ['6', '7', '8', '9', '10']]],
                ],
                'running-shoes' => [
                    ['name' => 'Trail Grip Running Shoes', 'price' => 3999, 'discount' => 3499, 'sizes' => ['shoe-size', ['8', '9', '10', '11']]],
                ],
            ],
            'stylehub-fashion' => [
                'jeans' => [
                    ['name' => 'Men Slim Fit Blue Jeans', 'price' => 1499, 'discount' => 1299, 'sizes' => ['clothing-size', ['S', 'M', 'L', 'XL', 'XXL']]],
                    ['name' => 'Women High Rise Skinny Jeans', 'price' => 1699, 'sizes' => ['clothing-size', ['S', 'M', 'L', 'XL']]],
                ],
                'shirts' => [
                    ['name' => 'Men Formal Cotton Shirt', 'price' => 1299, 'sizes' => ['clothing-size', ['S', 'M', 'L', 'XL', 'XXL']]],
                    ['name' => 'Women Office Wear Shirt', 'price' => 1199, 'discount' => 999, 'sizes' => ['clothing-size', ['S', 'M', 'L', 'XL']]],
                ],
                't-shirts' => [
                    ['name' => 'Unisex Round Neck T-Shirt', 'price' => 599, 'discount' => 499, 'sizes' => ['clothing-size', ['S', 'M', 'L', 'XL', 'XXL']]],
                    ['name' => 'Kids Graphic T-Shirt Pack', 'price' => 799, 'sizes' => ['clothing-size', ['XS', 'S', 'M', 'L']]],
                ],
                'kids-clothing' => [
                    ['name' => 'Kids Cotton Track Suit', 'price' => 999, 'sizes' => ['clothing-size', ['XS', 'S', 'M', 'L']]],
                ],
            ],
            'techzone-india' => [
                'mobiles' => [
                    ['name' => 'Smartphone X100 5G', 'price' => 18999, 'discount' => 16999, 'attrs' => ['storage', ['128GB', '256GB']]],
                    ['name' => 'Budget Phone Lite 4G', 'price' => 8999, 'sizes' => null],
                ],
                'laptops' => [
                    ['name' => 'Ultrabook 14" Laptop', 'price' => 54999, 'discount' => 49999, 'attrs' => ['storage', ['256GB', '512GB', '1TB']]],
                    ['name' => 'Student Laptop 15.6"', 'price' => 38999, 'attrs' => ['storage', ['256GB', '512GB']]],
                ],
                'accessories' => [
                    ['name' => 'Wireless Earbuds Pro', 'price' => 2499, 'discount' => 1999, 'sizes' => null],
                    ['name' => 'Bluetooth Speaker Mini', 'price' => 1799, 'sizes' => null],
                ],
            ],
            'kitchencraft-store' => [
                'dinner-sets' => [
                    ['name' => 'Ceramic Dinner Set Floral', 'price' => 3499, 'discount' => 2999, 'attrs' => ['set-pieces', ['12 Pieces', '24 Pieces']]],
                    ['name' => 'Premium Bone China Dinner Set', 'price' => 5999, 'attrs' => ['set-pieces', ['18 Pieces', '24 Pieces', '36 Pieces']]],
                ],
                'cookware' => [
                    ['name' => 'Non-Stick Cookware Set 5pc', 'price' => 2799, 'discount' => 2399, 'sizes' => null],
                    ['name' => 'Stainless Steel Kadai with Lid', 'price' => 1299, 'sizes' => null],
                ],
                'serveware' => [
                    ['name' => 'Serving Bowl Set Glass', 'price' => 1599, 'attrs' => ['set-pieces', ['12 Pieces', '18 Pieces']]],
                    ['name' => 'Tea Cup Saucer Set', 'price' => 999, 'discount' => 849, 'attrs' => ['set-pieces', ['12 Pieces', '24 Pieces']]],
                ],
            ],
        ];

        $brandIds = Brand::pluck('id');
        $colors = $this->colorValueIds();

        foreach ($catalog as $shopSlug => $categories) {
            $shop = Shop::where('slug', $shopSlug)->first();
            if (! $shop) {
                continue;
            }

            foreach ($categories as $categorySlug => $products) {
                $category = Category::where('slug', $categorySlug)->first();
                if (! $category) {
                    continue;
                }

                foreach ($products as $index => $item) {
                    $this->seedProduct($shop, $category, $item, $brandIds, $colors, $index);
                }
            }
        }
    }

    private function seedProduct(Shop $shop, Category $category, array $item, $brandIds, array $colors, int $index): void
    {
        $slug = Str::slug($item['name']);
        $brandId = $brandIds->isNotEmpty() ? $brandIds->random() : null;

        $product = Product::updateOrCreate(
            ['slug' => $slug],
            [
                'name' => $item['name'],
                'description' => $item['name'] . ' — quality product from ' . $shop->name . '. Prices in INR.',
                'short_description' => 'Available on MultiEcom marketplace.',
                'category_id' => $category->id,
                'brand_id' => $brandId,
                'shop_id' => $shop->id,
                'price' => $item['price'],
                'discount_price' => $item['discount'] ?? null,
                'stock_quantity' => 0,
                'manage_stock' => true,
                'in_stock' => true,
                'is_active' => true,
                'is_featured' => $index === 0,
                'sort_order' => $index + 1,
                'meta_title' => $item['name'],
                'meta_description' => 'Buy ' . $item['name'] . ' at best price in India (INR).',
            ]
        );

        $product->variants()->delete();

        $variantConfig = $item['sizes'] ?? $item['attrs'] ?? null;
        if (! $variantConfig) {
            $product->update([
                'stock_quantity' => random_int(20, 100),
                'in_stock' => true,
            ]);

            return;
        }

        [$attrSlug, $sizeValues] = $variantConfig;
        $attribute = Attribute::where('slug', $attrSlug)->first();
        if (! $attribute) {
            return;
        }

        $sizeIds = AttributeValue::where('attribute_id', $attribute->id)
            ->whereIn('value', $sizeValues)
            ->pluck('id', 'value');

        $colorSubset = array_slice($colors, 0, min(3, count($colors)));
        $colorAttribute = Attribute::where('slug', 'color')->first();

        foreach ($sizeValues as $sizeValue) {
            $sizeId = $sizeIds[$sizeValue] ?? null;
            if (! $sizeId) {
                continue;
            }

            foreach ($colorSubset as $colorId) {
                $variant = ProductVariant::create([
                    'product_id' => $product->id,
                    'stock_quantity' => random_int(5, 40),
                    'in_stock' => true,
                ]);

                $variant->attributes()->attach($attribute->id, [
                    'attribute_value_id' => $sizeId,
                ]);

                if ($colorAttribute) {
                    $variant->attributes()->attach($colorAttribute->id, [
                        'attribute_value_id' => $colorId,
                    ]);
                }
            }
        }
    }

    private function colorValueIds(): array
    {
        $colorAttr = Attribute::where('slug', 'color')->first();
        if (! $colorAttr) {
            return [];
        }

        return AttributeValue::where('attribute_id', $colorAttr->id)
            ->whereIn('value', ['black', 'blue', 'white'])
            ->pluck('id')
            ->all();
    }
}
