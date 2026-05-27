<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        $brands = [
            'Nike', 'Adidas', 'Puma', 'Reebok', 'Samsung', 'Apple', 'OnePlus',
            'Borosil', 'Milton', 'Prestige', 'Levi\'s', 'Allen Solly', 'H&M',
        ];

        foreach ($brands as $name) {
            Brand::updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'is_active' => true]
            );
        }

        $this->call([
            CategorySeeder::class,
            AttributeSeeder::class,
            VendorSeeder::class,
            ProductSeeder::class,
        ]);
    }
}
