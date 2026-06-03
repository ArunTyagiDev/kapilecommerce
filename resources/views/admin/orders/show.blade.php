@extends('layouts.admin')

@section('title', 'Order Details')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Order Details - {{ $order->order_number }}</h1>
    <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary">Back</a>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5>Order Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Order Number:</strong> {{ $order->order_number }}</p>
                        <p><strong>Order Date:</strong> {{ $order->created_at->format('F d, Y h:i A') }}</p>
                        <p><strong>Customer:</strong> {{ $order->name }}</p>
                        <p><strong>Email:</strong> {{ $order->email }}</p>
                        <p><strong>Phone:</strong> {{ $order->phone }}</p>
                    </div>
                    <div class="col-md-6">
                        <h6>Shipping Address:</h6>
                        <p>
                            {{ $order->address }}<br>
                            {{ $order->city }}, {{ $order->state }} {{ $order->pincode }}<br>
                            {{ $order->country }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5>Order Items</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Variant</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $item)
                            <tr>
                                <td>
                                    @if($item->custom_image_path)
                                        <img src="{{ asset('storage/' . $item->custom_image_path) }}" alt="Custom" style="width: 50px; height: 50px; object-fit: cover; margin-right: 10px; border:1px solid #0d6efd">
                                    @elseif($item->product->primaryImage)
                                        <img src="{{ asset('storage/' . $item->product->primaryImage->image_path) }}" alt="{{ $item->product_name }}" style="width: 50px; height: 50px; object-fit: cover; margin-right: 10px;">
                                    @endif
                                    {{ $item->product_name }}
                                    @if($item->custom_image_path)
                                        <br><a href="{{ asset('storage/'.$item->custom_image_path) }}" target="_blank" class="small">Customer photo</a>
                                    @endif
                                </td>
                                <td>
                                    @if($item->variant_details)
                                        {{ $item->variant_details }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>{{ $item->quantity }}</td>
                                <td>{{ format_inr($item->price) }}</td>
                                <td>{{ format_inr($item->subtotal) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4" class="text-end"><strong>Subtotal:</strong></td>
                                <td><strong>{{ format_inr($order->subtotal) }}</strong></td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-end"><strong>Delivery Charges:</strong></td>
                                <td><strong>{{ format_inr($order->delivery_charges) }}</strong></td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-end"><strong>Total:</strong></td>
                                <td><strong>{{ format_inr($order->total_amount) }}</strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5>Order Status</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.orders.update-status', $order) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label class="form-label">Current Status</label>
                        <select class="form-select" name="status" required>
                            <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="confirmed" {{ $order->status == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                            <option value="packed" {{ $order->status == 'packed' ? 'selected' : '' }}>Packed</option>
                            <option value="shipped" {{ $order->status == 'shipped' ? 'selected' : '' }}>Shipped</option>
                            <option value="delivered" {{ $order->status == 'delivered' ? 'selected' : '' }}>Delivered</option>
                            <option value="cancelled" {{ $order->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Update Status</button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5>Payment Information</h5>
            </div>
            <div class="card-body">
                <p><strong>Payment Method:</strong> {{ ucfirst(str_replace('_', ' ', $order->payment_method)) }}</p>
                <p><strong>Payment Status:</strong> 
                    <span class="badge bg-{{ $order->payment_status === 'success' ? 'success' : 'warning' }}">
                        {{ ucfirst($order->payment_status) }}
                    </span>
                </p>
                @if($order->payment)
                    <p><strong>Transaction ID:</strong> {{ $order->payment->transaction_id ?? 'N/A' }}</p>
                    @if($order->payment->paid_at)
                        <p><strong>Paid At:</strong> {{ $order->payment->paid_at->format('M d, Y h:i A') }}</p>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

