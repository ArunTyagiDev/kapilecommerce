@extends('layouts.vendor')

@section('title', 'Dashboard')

@section('content')
<div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">{{ $shop->name }}</h1>
    <a href="{{ route('vendor.products.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Add Product</a>
</div>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <h6>Total Products</h6>
                <h2>{{ $stats['products'] }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-success">
            <div class="card-body">
                <h6>Active Products</h6>
                <h2>{{ $stats['active_products'] }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-info">
            <div class="card-body">
                <h6>Orders (items)</h6>
                <h2>{{ $stats['orders'] }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-dark">
            <div class="card-body">
                <h6>Revenue (INR)</h6>
                <h2>{{ format_inr($stats['revenue'], 0) }}</h2>
            </div>
        </div>
    </div>
</div>

<h4 class="mb-3">Recent Products</h4>
<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Name</th>
                <th>Category</th>
                <th>Price (INR)</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($recentProducts as $product)
            <tr>
                <td>{{ $product->name }}</td>
                <td>{{ $product->category->name }}</td>
                <td>{{ format_inr($product->final_price) }}</td>
                <td><a href="{{ route('vendor.products.edit', $product) }}" class="btn btn-sm btn-outline-primary">Edit</a></td>
            </tr>
            @empty
            <tr><td colspan="4" class="text-muted">No products yet. <a href="{{ route('vendor.products.create') }}">Add your first product</a>.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
