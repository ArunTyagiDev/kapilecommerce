<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function index()
    {
        $sessionId = session()->getId();
        $userId = Auth::id();

        $cartItems = Cart::with(['product.images', 'variant.attributeValues'])
            ->where(function($query) use ($sessionId, $userId) {
                if ($userId) {
                    $query->where('user_id', $userId);
                } else {
                    $query->where('session_id', $sessionId);
                }
            })
            ->get();

        $total = $cartItems->sum('subtotal');

        return view('frontend.cart.index', compact('cartItems', 'total'));
    }

    public function add(Request $request)
    {
        $product = Product::findOrFail($request->product_id);

        $rules = [
            'product_id' => 'required|exists:products,id',
            'variant_id' => 'nullable|exists:product_variants,id',
            'quantity' => 'required|integer|min:1',
        ];

        if ($product->is_customizable && $product->customization_type !== 'text_sticker') {
            $rules['custom_photo'] = 'required|image|mimes:jpeg,jpg,png,webp|max:10240';
        }

        if ($product->customization_type === 'text_sticker') {
            $rules['student_name'] = 'required|string|max:120';
            $rules['student_class'] = 'required|string|max:80';
            $rules['school_name'] = 'nullable|string|max:150';
            $rules['contact_phone'] = 'nullable|string|max:20';
        }

        $validated = $request->validate($rules);
        
        // Determine price
        if ($validated['variant_id']) {
            $variant = ProductVariant::findOrFail($validated['variant_id']);
            $price = $variant->final_price;
            $stock = $variant->stock_quantity;
        } else {
            $price = $product->final_price;
            $stock = $product->stock_quantity;
        }

        // Check stock
        if ($stock < $validated['quantity']) {
            return redirect()->back()->with('error', 'Insufficient stock.');
        }

        $sessionId = session()->getId();
        $userId = Auth::id();

        $customImagePath = null;
        $customizationData = null;

        if ($product->is_customizable && $request->hasFile('custom_photo')) {
            $customImagePath = $request->file('custom_photo')->store('custom-uploads', 'public');
            $customizationData = [
                'size_label' => $request->input('size_label'),
                'thickness_label' => $request->input('thickness_label'),
                'shape' => $product->shape_label,
            ];
        }

        if ($product->customization_type === 'text_sticker') {
            $customizationData = [
                'type' => 'text_sticker',
                'student_name' => $request->input('student_name'),
                'student_class' => $request->input('student_class'),
                'school_name' => $request->input('school_name'),
                'contact_phone' => $request->input('contact_phone'),
                'pack' => '30 pcs',
            ];
        }

        // Custom uploads always get a new line; standard products merge by variant
        $cartItem = null;
        if (! $product->is_customizable) {
            $cartItem = Cart::where('product_id', $validated['product_id'])
                ->where('variant_id', $validated['variant_id'] ?? null)
                ->where(function ($query) use ($sessionId, $userId) {
                    if ($userId) {
                        $query->where('user_id', $userId);
                    } else {
                        $query->where('session_id', $sessionId);
                    }
                })
                ->first();
        }

        if ($cartItem) {
            $cartItem->quantity += $validated['quantity'];
            $cartItem->save();
        } else {
            Cart::create([
                'session_id' => $userId ? null : $sessionId,
                'user_id' => $userId,
                'product_id' => $validated['product_id'],
                'variant_id' => $validated['variant_id'] ?? null,
                'quantity' => $validated['quantity'],
                'price' => $price,
                'custom_image_path' => $customImagePath,
                'customization_data' => $customizationData,
            ]);
        }

        return redirect()->route('cart.index')->with('success', 'Product added to cart.');
    }

    public function update(Request $request, Cart $cart)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $cart->update(['quantity' => $validated['quantity']]);

        return redirect()->back()->with('success', 'Cart updated.');
    }

    public function destroy(Cart $cart)
    {
        $cart->delete();
        return redirect()->back()->with('success', 'Item removed from cart.');
    }
}

