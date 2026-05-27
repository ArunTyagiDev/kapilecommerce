<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Shoes',
                'children' => [
                    'Men Shoes',
                    'Women Shoes',
                    'Kids Shoes',
                    'Running Shoes',
                ],
            ],
            [
                'name' => 'Clothes',
                'children' => [
                    'Jeans',
                    'Shirts',
                    'T-Shirts',
                    'Kids Clothing',
                ],
            ],
            [
                'name' => 'Electronics',
                'children' => [
                    'Mobiles',
                    'Laptops',
                    'Accessories',
                ],
            ],
            [
                'name' => 'Crockery',
                'children' => [
                    'Dinner Sets',
                    'Cookware',
                    'Serveware',
                ],
            ],
        ];

        $sortOrder = 1;
        foreach ($categories as $group) {
            $parent = Category::updateOrCreate(
                ['slug' => Str::slug($group['name'])],
                [
                    'name' => $group['name'],
                    'parent_id' => null,
                    'sort_order' => $sortOrder++,
                    'is_active' => true,
                ]
            );

            $childOrder = 1;
            foreach ($group['children'] as $childName) {
                Category::updateOrCreate(
                    ['slug' => Str::slug($childName)],
                    [
                        'name' => $childName,
                        'parent_id' => $parent->id,
                        'sort_order' => $childOrder++,
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}
