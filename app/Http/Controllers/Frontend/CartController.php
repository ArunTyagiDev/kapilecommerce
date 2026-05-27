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
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'variant_id' => 'nullable|exists:product_variants,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::findOrFail($validated['product_id']);
        
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

        // Check if item already exists in cart
        $cartItem = Cart::where('product_id', $validated['product_id'])
            ->where('variant_id', $validated['variant_id'])
            ->where(function($query) use ($sessionId, $userId) {
                if ($userId) {
                    $query->where('user_id', $userId);
                } else {
                    $query->where('session_id', $sessionId);
                }
            })
            ->first();

        if ($cartItem) {
            $cartItem->quantity += $validated['quantity'];
            $cartItem->save();
        } else {
            Cart::create([
                'session_id' => $userId ? null : $sessionId,
                'user_id' => $userId,
                'product_id' => $validated['product_id'],
                'variant_id' => $validated['variant_id'],
                'quantity' => $validated['quantity'],
                'price' => $price,
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

