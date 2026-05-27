@extends('layouts.frontend')

@section('title', 'Shopping Cart')

@section('content')
<h2>Shopping Cart</h2>

@if($cartItems->count() > 0)
<div class="table-responsive">
    <table class="table">
        <thead>
            <tr>
                <th>Product</th>
                <th>Variant</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Subtotal</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($cartItems as $item)
            <tr>
                <td>
                    <div class="d-flex align-items-center">
                        @if($item->product->primaryImage)
                            <img src="{{ asset('storage/' . $item->product->primaryImage->image_path) }}" alt="{{ $item->product->name }}" style="width: 80px; height: 80px; object-fit: cover; margin-right: 10px;">
                        @endif
                        <div>
                            <strong>{{ $item->product->name }}</strong><br>
                            <small class="text-muted">{{ $item->product->sku }}</small>
                        </div>
                    </div>
                </td>
                <td>
                    @if($item->variant)
                        @foreach($item->variant->attributeValues as $value)
                            <span class="badge bg-info">{{ $value->attribute->name }}: {{ $value->display_value ?? $value->value }}</span>
                        @endforeach
                    @else
                        <span class="text-muted">-</span>
                    @endif
                </td>
                <td>{{ format_inr($item->price) }}</td>
                <td>
                    <form action="{{ route('cart.update', $item) }}" method="POST" class="d-inline">
                        @csrf
                        @method('PUT')
                        <input type="number" name="quantity" value="{{ $item->quantity }}" min="1" style="width: 80px;" class="form-control d-inline" onchange="this.form.submit()">
                    </form>
                </td>
                <td>{{ format_inr($item->subtotal) }}</td>
                <td>
                    <form action="{{ route('cart.destroy', $item) }}" method="POST" class="d-inline" onsubmit="return confirm('Remove this item?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="text-end"><strong>Total:</strong></td>
                <td><strong>{{ format_inr($total) }}</strong></td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</div>

        <div class="text-end mt-4">
            <a href="{{ route('products.index') }}" class="btn btn-secondary">Continue Shopping</a>
            <a href="{{ route('checkout.index') }}" class="btn btn-primary btn-lg">Proceed to Checkout</a>
        </div>
@else
<div class="alert alert-info">
    <h4>Your cart is empty</h4>
    <p>Start shopping to add items to your cart.</p>
    <a href="{{ route('products.index') }}" class="btn btn-primary">Browse Products</a>
</div>
@endif
@endsection

