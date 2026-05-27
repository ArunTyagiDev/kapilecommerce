@extends('layouts.frontend')

@section('title', 'My Orders')

@section('content')
<div class="row">
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <h5>Welcome, {{ Auth::user()->name }}</h5>
                <hr>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="{{ route('user.dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                    <li class="mb-2"><a href="{{ route('user.orders') }}" class="text-decoration-none">My Orders</a></li>
                    <li class="mb-2"><a href="{{ route('user.profile') }}" class="text-decoration-none">Profile</a></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="col-md-9">
        <h2>My Orders</h2>

        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Order #</th>
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
                        <td>{{ $order->created_at->format('M d, Y') }}</td>
                        <td>{{ $order->items->count() }} item(s)</td>
                        <td>{{ format_inr($order->total_amount) }}</td>
                        <td>
                            <span class="badge bg-info">{{ ucfirst(str_replace('_', ' ', $order->payment_method)) }}</span>
                        </td>
                        <td><span class="badge bg-{{ $order->status_badge }}">{{ ucfirst($order->status) }}</span></td>
                        <td>
                            <a href="{{ route('user.order-details', $order->id) }}" class="btn btn-sm btn-primary">View Details</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center">No orders found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $orders->links() }}
    </div>
</div>
@endsection

