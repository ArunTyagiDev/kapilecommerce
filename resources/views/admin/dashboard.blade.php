@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Dashboard</h1>
</div>

<!-- Stats Widgets -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <h5 class="card-title">Total Sales</h5>
                <h2>{{ format_inr($stats['total_sales']) }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-success">
            <div class="card-body">
                <h5 class="card-title">Total Orders</h5>
                <h2>{{ $stats['total_orders'] }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-info">
            <div class="card-body">
                <h5 class="card-title">Today Orders</h5>
                <h2>{{ $stats['today_orders'] }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-warning">
            <div class="card-body">
                <h5 class="card-title">Today's Earnings</h5>
                <h2>{{ format_inr($stats['today_earnings']) }}</h2>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-white bg-secondary">
            <div class="card-body">
                <h5 class="card-title">Total Products</h5>
                <h2>{{ $stats['total_products'] }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-dark">
            <div class="card-body">
                <h5 class="card-title">Categories</h5>
                <h2>{{ $stats['total_categories'] }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-danger">
            <div class="card-body">
                <h5 class="card-title">Low Stock</h5>
                <h2>{{ $stats['low_stock_products'] }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white" style="background-color: #6f42c1;">
            <div class="card-body">
                <h5 class="card-title">Active Products</h5>
                <h2>{{ $stats['active_products'] }}</h2>
            </div>
        </div>
    </div>
</div>

<!-- Sales Chart -->
<div class="row mb-4">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5>Sales Overview (Last 7 Days)</h5>
            </div>
            <div class="card-body">
                <canvas id="salesChart" height="100"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5>Top Selling Products</h5>
            </div>
            <div class="card-body">
                @forelse($topSellingProducts as $product)
                <div class="d-flex justify-content-between mb-2">
                    <span>{{ $product->name }}</span>
                    <strong>{{ $product->total_sold }} sold</strong>
                </div>
                @empty
                <p class="text-muted">No sales data</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Recent Orders -->
<div class="row">
    <div class="col-md-12">
        <h3>Recent Orders</h3>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentOrders as $order)
                    <tr>
                        <td>{{ $order->order_number }}</td>
                        <td>{{ $order->name }}</td>
                        <td>{{ $order->created_at->format('M d, Y') }}</td>
                        <td>{{ format_inr($order->total_amount) }}</td>
                        <td><span class="badge bg-{{ $order->status_badge }}">{{ ucfirst($order->status) }}</span></td>
                        <td>
                            <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-primary">View</a>
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
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const salesData = @json($salesData);
    const ctx = document.getElementById('salesChart').getContext('2d');
    const chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: salesData.map(item => item.date),
            datasets: [{
                label: 'Sales ($)',
                data: salesData.map(item => item.sales),
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>
@endpush

