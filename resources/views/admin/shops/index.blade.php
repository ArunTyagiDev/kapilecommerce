@extends('layouts.admin')

@section('title', 'Vendor Shops')

@section('content')
<div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Vendor Shops</h1>
    <a href="{{ route('admin.shops.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Add Vendor</a>
</div>

<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Shop</th>
                <th>Owner</th>
                <th>Email</th>
                <th>City</th>
                <th>Products</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($shops as $shop)
            <tr>
                <td>{{ $shop->name }}</td>
                <td>{{ $shop->owner->name }}</td>
                <td>{{ $shop->email }}</td>
                <td>{{ $shop->city }}</td>
                <td>{{ $shop->products_count }}</td>
                <td>
                    @if($shop->is_approved && $shop->is_active)
                        <span class="badge bg-success">Active</span>
                    @else
                        <span class="badge bg-warning">Inactive</span>
                    @endif
                </td>
                <td>
                    <a href="{{ route('admin.shops.edit', $shop) }}" class="btn btn-sm btn-primary">Edit</a>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="text-muted">No vendor shops yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
{{ $shops->links() }}
@endsection
