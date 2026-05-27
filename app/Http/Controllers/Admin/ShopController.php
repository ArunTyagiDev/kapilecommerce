<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ShopController extends Controller
{
    public function index()
    {
        $shops = Shop::with('owner')->withCount('products')->latest()->paginate(20);

        return view('admin.shops.index', compact('shops'));
    }

    public function create()
    {
        return view('admin.shops.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'owner_name' => 'required|string|max:255',
            'owner_email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'shop_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'pincode' => 'nullable|string|max:10',
            'is_active' => 'boolean',
            'is_approved' => 'boolean',
        ]);

        $user = User::create([
            'name' => $validated['owner_name'],
            'email' => $validated['owner_email'],
            'password' => Hash::make($validated['password']),
            'role' => 'vendor',
        ]);

        Shop::create([
            'user_id' => $user->id,
            'name' => $validated['shop_name'],
            'slug' => Str::slug($validated['shop_name']),
            'description' => $validated['description'] ?? null,
            'email' => $validated['owner_email'],
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'city' => $validated['city'] ?? null,
            'state' => $validated['state'] ?? null,
            'pincode' => $validated['pincode'] ?? null,
            'is_active' => $request->boolean('is_active', true),
            'is_approved' => $request->boolean('is_approved', true),
        ]);

        return redirect()->route('admin.shops.index')
            ->with('success', 'Vendor shop created successfully.');
    }

    public function edit(Shop $shop)
    {
        $shop->load('owner');

        return view('admin.shops.edit', compact('shop'));
    }

    public function update(Request $request, Shop $shop)
    {
        $validated = $request->validate([
            'shop_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'pincode' => 'nullable|string|max:10',
            'is_active' => 'boolean',
            'is_approved' => 'boolean',
        ]);

        $shop->update([
            'name' => $validated['shop_name'],
            'slug' => Str::slug($validated['shop_name']),
            'description' => $validated['description'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'city' => $validated['city'] ?? null,
            'state' => $validated['state'] ?? null,
            'pincode' => $validated['pincode'] ?? null,
            'is_active' => $request->boolean('is_active'),
            'is_approved' => $request->boolean('is_approved'),
        ]);

        return redirect()->route('admin.shops.index')
            ->with('success', 'Shop updated successfully.');
    }
}
