<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\UserAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
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

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        $subtotal = $cartItems->sum('subtotal');
        $deliveryCharges = $this->calculateDeliveryCharges($subtotal);
        $total = $subtotal + $deliveryCharges;

        $userAddresses = [];
        if ($userId) {
            $userAddresses = UserAddress::where('user_id', $userId)->get();
        }

        return view('frontend.checkout', compact('cartItems', 'subtotal', 'deliveryCharges', 'total', 'userAddresses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'pincode' => 'required|string|max:10',
            'country' => 'required|string|max:255',
            'payment_method' => 'required|in:phonepe,paytm,google_pay,cod',
            'notes' => 'nullable|string',
        ]);

        $sessionId = session()->getId();
        $userId = Auth::id();

        $cartItems = Cart::with(['product', 'variant.attributeValues'])
            ->where(function($query) use ($sessionId, $userId) {
                if ($userId) {
                    $query->where('user_id', $userId);
                } else {
                    $query->where('session_id', $sessionId);
                }
            })
            ->get();

        if ($cartItems->isEmpty()) {
            return redirect()->back()->with('error', 'Your cart is empty.');
        }

        DB::beginTransaction();
        try {
            $subtotal = $cartItems->sum('subtotal');
            $deliveryCharges = $this->calculateDeliveryCharges($subtotal);
            $total = $subtotal + $deliveryCharges;

            // Create order
            $order = Order::create([
                'user_id' => $userId,
                'session_id' => $userId ? null : $sessionId,
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'address' => $validated['address'],
                'city' => $validated['city'],
                'state' => $validated['state'],
                'pincode' => $validated['pincode'],
                'country' => $validated['country'],
                'subtotal' => $subtotal,
                'delivery_charges' => $deliveryCharges,
                'total_amount' => $total,
                'payment_method' => $validated['payment_method'],
                'payment_status' => $validated['payment_method'] === 'cod' ? 'pending' : 'pending',
                'status' => 'pending',
                'notes' => $validated['notes'] ?? null,
            ]);

            // Create order items
            foreach ($cartItems as $cartItem) {
                $variantDetails = null;
                if ($cartItem->variant) {
                    $variantDetails = $cartItem->variant->attributeValues->map(function($value) {
                        return $value->attribute->name . ': ' . ($value->display_value ?? $value->value);
                    })->implode(', ');
                }

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $cartItem->product_id,
                    'shop_id' => $cartItem->product->shop_id,
                    'variant_id' => $cartItem->variant_id,
                    'product_name' => $cartItem->product->name,
                    'variant_details' => $variantDetails,
                    'quantity' => $cartItem->quantity,
                    'price' => $cartItem->price,
                    'subtotal' => $cartItem->subtotal,
                ]);
            }

            // Create payment record
            Payment::create([
                'order_id' => $order->id,
                'payment_method' => $validated['payment_method'],
                'amount' => $total,
                'status' => $validated['payment_method'] === 'cod' ? 'pending' : 'pending',
            ]);

            // Save address for logged-in users
            if ($userId && $request->has('save_address')) {
                UserAddress::updateOrCreate(
                    [
                        'user_id' => $userId,
                        'is_default' => true,
                    ],
                    [
                        'name' => $validated['name'],
                        'email' => $validated['email'],
                        'phone' => $validated['phone'],
                        'address' => $validated['address'],
                        'city' => $validated['city'],
                        'state' => $validated['state'],
                        'pincode' => $validated['pincode'],
                        'country' => $validated['country'],
                    ]
                );
            }

            // Clear cart
            Cart::where(function($query) use ($sessionId, $userId) {
                if ($userId) {
                    $query->where('user_id', $userId);
                } else {
                    $query->where('session_id', $sessionId);
                }
            })->delete();

            DB::commit();

            if ($userId) {
                return redirect()->route('user.dashboard')->with('success', 'Order placed successfully! Order #' . $order->order_number);
            } else {
                return redirect()->route('products.index')->with('success', 'Order placed successfully! Order #' . $order->order_number);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error placing order: ' . $e->getMessage())->withInput();
        }
    }

    private function calculateDeliveryCharges($subtotal)
    {
        $freeAbove = config('shop.free_delivery_above', 500);
        $charge = config('shop.default_delivery_charge', 50);

        return $subtotal >= $freeAbove ? 0 : $charge;
    }
}

