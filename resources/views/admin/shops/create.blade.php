@extends('layouts.admin')

@section('title', 'Add Vendor Shop')

@section('content')
<div class="pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Add Vendor Shop</h1>
</div>

<form action="{{ route('admin.shops.store') }}" method="POST" class="row g-3">
    @csrf
    <div class="col-md-6">
        <label class="form-label">Owner Name *</label>
        <input type="text" name="owner_name" class="form-control" value="{{ old('owner_name') }}" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">Owner Email *</label>
        <input type="email" name="owner_email" class="form-control" value="{{ old('owner_email') }}" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">Password *</label>
        <input type="password" name="password" class="form-control" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">Shop Name *</label>
        <input type="text" name="shop_name" class="form-control" value="{{ old('shop_name') }}" required>
    </div>
    <div class="col-12">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="2">{{ old('description') }}</textarea>
    </div>
    <div class="col-md-4">
        <label class="form-label">Phone</label>
        <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">City</label>
        <input type="text" name="city" class="form-control" value="{{ old('city') }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">State</label>
        <input type="text" name="state" class="form-control" value="{{ old('state') }}">
    </div>
    <div class="col-md-6">
        <label class="form-label">Pincode</label>
        <input type="text" name="pincode" class="form-control" value="{{ old('pincode') }}">
    </div>
    <div class="col-md-6">
        <div class="form-check mt-4">
            <input class="form-check-input" type="checkbox" name="is_active" value="1" checked>
            <label class="form-check-label">Active</label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="is_approved" value="1" checked>
            <label class="form-check-label">Approved</label>
        </div>
    </div>
    <div class="col-12">
        <button type="submit" class="btn btn-primary">Create Vendor</button>
        <a href="{{ route('admin.shops.index') }}" class="btn btn-secondary">Cancel</a>
    </div>
</form>
@endsection
