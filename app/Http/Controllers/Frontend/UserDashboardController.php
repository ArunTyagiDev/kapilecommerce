<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\UserAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = Auth::user();
        $orders = Order::where('user_id', $user->id)
            ->with(['items.product', 'payment'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $addresses = UserAddress::where('user_id', $user->id)->get();

        return view('frontend.user.dashboard', compact('orders', 'addresses', 'user'));
    }

    public function orders()
    {
        $user = Auth::user();
        $orders = Order::where('user_id', $user->id)
            ->with(['items.product', 'payment'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('frontend.user.orders', compact('orders'));
    }

    public function orderDetails($id)
    {
        $user = Auth::user();
        $order = Order::where('id', $id)
            ->where('user_id', $user->id)
            ->with(['items.product.images', 'items.variant.attributeValues', 'payment'])
            ->firstOrFail();

        return view('frontend.user.order-details', compact('order'));
    }

    public function profile()
    {
        $user = Auth::user();
        $addresses = UserAddress::where('user_id', $user->id)->get();

        return view('frontend.user.profile', compact('user', 'addresses'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
        ]);

        $user->update($validated);

        return redirect()->back()->with('success', 'Profile updated successfully.');
    }

    public function saveAddress(Request $request)
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
            'is_default' => 'boolean',
        ]);

        $validated['user_id'] = Auth::id();

        if ($validated['is_default'] ?? false) {
            UserAddress::where('user_id', Auth::id())->update(['is_default' => false]);
        }

        UserAddress::create($validated);

        return redirect()->back()->with('success', 'Address saved successfully.');
    }

    public function deleteAddress($id)
    {
        $address = UserAddress::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $address->delete();

        return redirect()->back()->with('success', 'Address deleted successfully.');
    }
}

