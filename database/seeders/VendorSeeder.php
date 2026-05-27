<?php

namespace Database\Seeders;

use App\Models\Shop;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class VendorSeeder extends Seeder
{
    public function run(): void
    {
        $vendors = [
            [
                'name' => 'SoleMate Footwear',
                'email' => 'shoes@vendor.com',
                'city' => 'Mumbai',
                'state' => 'Maharashtra',
            ],
            [
                'name' => 'StyleHub Fashion',
                'email' => 'clothes@vendor.com',
                'city' => 'Delhi',
                'state' => 'Delhi',
            ],
            [
                'name' => 'TechZone India',
                'email' => 'electronics@vendor.com',
                'city' => 'Bengaluru',
                'state' => 'Karnataka',
            ],
            [
                'name' => 'KitchenCraft Store',
                'email' => 'crockery@vendor.com',
                'city' => 'Jaipur',
                'state' => 'Rajasthan',
            ],
        ];

        foreach ($vendors as $vendor) {
            $user = User::updateOrCreate(
                ['email' => $vendor['email']],
                [
                    'name' => $vendor['name'] . ' Owner',
                    'password' => Hash::make('password'),
                    'role' => 'vendor',
                ]
            );

            Shop::updateOrCreate(
                ['slug' => Str::slug($vendor['name'])],
                [
                    'user_id' => $user->id,
                    'name' => $vendor['name'],
                    'description' => 'Trusted seller on MultiEcom marketplace.',
                    'email' => $vendor['email'],
                    'phone' => '9876543210',
                    'city' => $vendor['city'],
                    'state' => $vendor['state'],
                    'pincode' => '110001',
                    'is_active' => true,
                    'is_approved' => true,
                ]
            );
        }
    }
}
