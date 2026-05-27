<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Category;
use Illuminate\Database\Seeder;

class AttributeSeeder extends Seeder
{
    public function run(): void
    {
        $attributes = [
            'shoe-size' => [
                'name' => 'Shoe Size (UK)',
                'values' => ['6', '7', '8', '9', '10', '11'],
            ],
            'kids-shoe-size' => [
                'name' => 'Kids Shoe Size',
                'values' => ['UK 8', 'UK 9', 'UK 10', 'UK 11', 'UK 12', 'UK 13'],
            ],
            'clothing-size' => [
                'name' => 'Clothing Size',
                'values' => ['XS', 'S', 'M', 'L', 'XL', 'XXL'],
            ],
            'color' => [
                'name' => 'Color',
                'values' => [
                    ['value' => 'black', 'display' => 'Black', 'code' => '#000000'],
                    ['value' => 'white', 'display' => 'White', 'code' => '#FFFFFF'],
                    ['value' => 'red', 'display' => 'Red', 'code' => '#FF0000'],
                    ['value' => 'blue', 'display' => 'Blue', 'code' => '#0000FF'],
                    ['value' => 'green', 'display' => 'Green', 'code' => '#008000'],
                    ['value' => 'navy', 'display' => 'Navy', 'code' => '#001F3F'],
                ],
            ],
            'storage' => [
                'name' => 'Storage',
                'values' => ['64GB', '128GB', '256GB', '512GB', '1TB'],
            ],
            'set-pieces' => [
                'name' => 'Set Pieces',
                'values' => ['12 Pieces', '18 Pieces', '24 Pieces', '36 Pieces'],
            ],
        ];

        $created = [];
        foreach ($attributes as $slug => $config) {
            $attribute = Attribute::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $config['name'],
                    'type' => 'select',
                    'is_filterable' => true,
                    'is_required' => false,
                ]
            );

            $sort = 1;
            foreach ($config['values'] as $value) {
                if (is_array($value)) {
                    AttributeValue::updateOrCreate(
                        [
                            'attribute_id' => $attribute->id,
                            'value' => $value['value'],
                        ],
                        [
                            'display_value' => $value['display'],
                            'color_code' => $value['code'] ?? null,
                            'sort_order' => $sort++,
                        ]
                    );
                } else {
                    AttributeValue::updateOrCreate(
                        [
                            'attribute_id' => $attribute->id,
                            'value' => $value,
                        ],
                        [
                            'display_value' => $value,
                            'sort_order' => $sort++,
                        ]
                    );
                }
            }

            $created[$slug] = $attribute;
        }

        $this->attachCategoryAttributes($created);
    }

    private function attachCategoryAttributes(array $attributes): void
    {
        $map = [
            'shoes' => [
                ['slug' => 'men-shoes', 'attrs' => ['shoe-size', 'color']],
                ['slug' => 'women-shoes', 'attrs' => ['shoe-size', 'color']],
                ['slug' => 'running-shoes', 'attrs' => ['shoe-size', 'color']],
                ['slug' => 'kids-shoes', 'attrs' => ['kids-shoe-size', 'color']],
            ],
            'clothes' => [
                ['slug' => 'jeans', 'attrs' => ['clothing-size', 'color']],
                ['slug' => 'shirts', 'attrs' => ['clothing-size', 'color']],
                ['slug' => 't-shirts', 'attrs' => ['clothing-size', 'color']],
                ['slug' => 'kids-clothing', 'attrs' => ['clothing-size', 'color']],
            ],
            'electronics' => [
                ['slug' => 'mobiles', 'attrs' => ['storage', 'color']],
                ['slug' => 'laptops', 'attrs' => ['storage', 'color']],
                ['slug' => 'accessories', 'attrs' => ['color']],
            ],
            'crockery' => [
                ['slug' => 'dinner-sets', 'attrs' => ['set-pieces', 'color']],
                ['slug' => 'cookware', 'attrs' => ['color']],
                ['slug' => 'serveware', 'attrs' => ['set-pieces', 'color']],
            ],
        ];

        foreach ($map as $parentSlug => $children) {
            $parent = Category::where('slug', $parentSlug)->first();
            if (! $parent) {
                continue;
            }

            foreach ($children as $childConfig) {
                $category = Category::where('slug', $childConfig['slug'])->first();
                if (! $category) {
                    continue;
                }

                $sync = [];
                foreach ($childConfig['attrs'] as $index => $attrSlug) {
                    if (isset($attributes[$attrSlug])) {
                        $sync[$attributes[$attrSlug]->id] = [
                            'is_required' => $index === 0,
                            'sort_order' => $index + 1,
                        ];
                    }
                }

                $category->attributes()->sync($sync);
            }
        }
    }
}
