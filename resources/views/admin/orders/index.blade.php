@extends('layouts.admin')

@section('title', 'Orders')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Orders Management</h1>
</div>

<!-- Filters -->
<form method="GET" action="{{ route('admin.orders.index') }}" class="mb-4">
    <div class="row g-3">
        <div class="col-md-3">
            <input type="text" class="form-control" name="search" placeholder="Search order number, name, email..." value="{{ request('search') }}">
        </div>
        <div class="col-md-2">
            <select class="form-select" name="status">
                <option value="">All Status</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                <option value="packed" {{ request('status') == 'packed' ? 'selected' : '' }}>Packed</option>
                <option value="shipped" {{ request('status') == 'shipped' ? 'selected' : '' }}>Shipped</option>
                <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>Delivered</option>
                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
        </div>
        <div class="col-md-2">
            <select class="form-select" name="payment_method">
                <option value="">All Payment Methods</option>
                <option value="phonepe" {{ request('payment_method') == 'phonepe' ? 'selected' : '' }}>PhonePe</option>
                <option value="paytm" {{ request('payment_method') == 'paytm' ? 'selected' : '' }}>Paytm</option>
                <option value="google_pay" {{ request('payment_method') == 'google_pay' ? 'selected' : '' }}>Google Pay</option>
                <option value="cod" {{ request('payment_method') == 'cod' ? 'selected' : '' }}>Cash on Delivery</option>
            </select>
        </div>
        <div class="col-md-2">
            <input type="date" class="form-control" name="date_from" value="{{ request('date_from') }}" placeholder="From Date">
        </div>
        <div class="col-md-2">
            <input type="date" class="form-control" name="date_to" value="{{ request('date_to') }}" placeholder="To Date">
        </div>
        <div class="col-md-1">
            <button type="submit" class="btn btn-primary w-100">Filter</button>
        </div>
    </div>
</form>

<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Order #</th>
                <th>Customer</th>
                <th>Date</th>
                <th>Items</th>
                <th>Total</th>
                <th>Payment</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($orders as $order)
            <tr>
                <td>{{ $order->order_number }}</td>
                <td>
                    {{ $order->name }}<br>
                    <small class="text-muted">{{ $order->email }}</small>
                </td>
                <td>{{ $order->created_at->format('M d, Y') }}</td>
                <td>{{ $order->items->count() }} item(s)</td>
                <td>{{ format_inr($order->total_amount) }}</td>
                <td>
                    <span class="badge bg-info">{{ ucfirst(str_replace('_', ' ', $order->payment_method)) }}</span>
                </td>
                <td>
                    <span class="badge bg-{{ $order->status_badge }}">{{ ucfirst($order->status) }}</span>
                </td>
                <td>
                    <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-primary">
                        <i class="bi bi-eye"></i> View
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center">No orders found</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{ $orders->links() }}
@endsection

