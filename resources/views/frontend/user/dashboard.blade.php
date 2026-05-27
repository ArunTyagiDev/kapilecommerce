@extends('layouts.frontend')

@section('title', 'My Dashboard')

@section('content')
<div class="row">
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <h5>Welcome, {{ $user->name }}</h5>
                <hr>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="{{ route('user.dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                    <li class="mb-2"><a href="{{ route('user.orders') }}" class="text-decoration-none">My Orders</a></li>
                    <li class="mb-2"><a href="{{ route('user.profile') }}" class="text-decoration-none">Profile</a></li>
                    <li class="mb-2">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="btn btn-link text-decoration-none p-0">Logout</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="col-md-9">
        <h2>My Dashboard</h2>

        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card text-white bg-primary">
                    <div class="card-body">
                        <h5>Total Orders</h5>
                        <h2>{{ $orders->total() }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white bg-success">
                    <div class="card-body">
                        <h5>Delivered</h5>
                        <h2>{{ $orders->where('status', 'delivered')->count() }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white bg-warning">
                    <div class="card-body">
                        <h5>Pending</h5>
                        <h2>{{ $orders->where('status', 'pending')->count() }}</h2>
                    </div>
                </div>
            </div>
        </div>

        <h4>Recent Orders</h4>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Date</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders->take(5) as $order)
                    <tr>
                        <td>{{ $order->order_number }}</td>
                        <td>{{ $order->created_at->format('M d, Y') }}</td>
                        <td>{{ $order->items->count() }} item(s)</td>
                        <td>{{ format_inr($order->total_amount) }}</td>
                        <td><span class="badge bg-{{ $order->status_badge }}">{{ ucfirst($order->status) }}</span></td>
                        <td>
                            <a href="{{ route('user.order-details', $order->id) }}" class="btn btn-sm btn-primary">View</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center">No orders found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            <a href="{{ route('user.orders') }}" class="btn btn-primary">View All Orders</a>
        </div>
    </div>
</div>
@endsection

