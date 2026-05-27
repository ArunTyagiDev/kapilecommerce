@extends('layouts.admin')

@section('title', 'Edit Shop')

@section('content')
<div class="pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Edit: {{ $shop->name }}</h1>
    <p class="text-muted mb-0">Owner: {{ $shop->owner->email }}</p>
</div>

<form action="{{ route('admin.shops.update', $shop) }}" method="POST" class="row g-3">
    @csrf
    @method('PUT')
    <div class="col-md-6">
        <label class="form-label">Shop Name *</label>
        <input type="text" name="shop_name" class="form-control" value="{{ old('shop_name', $shop->name) }}" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">Phone</label>
        <input type="text" name="phone" class="form-control" value="{{ old('phone', $shop->phone) }}">
    </div>
    <div class="col-12">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="2">{{ old('description', $shop->description) }}</textarea>
    </div>
    <div class="col-md-4">
        <label class="form-label">City</label>
        <input type="text" name="city" class="form-control" value="{{ old('city', $shop->city) }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">State</label>
        <input type="text" name="state" class="form-control" value="{{ old('state', $shop->state) }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">Pincode</label>
        <input type="text" name="pincode" class="form-control" value="{{ old('pincode', $shop->pincode) }}">
    </div>
    <div class="col-12">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="is_active" value="1" {{ $shop->is_active ? 'checked' : '' }}>
            <label class="form-check-label">Active</label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="is_approved" value="1" {{ $shop->is_approved ? 'checked' : '' }}>
            <label class="form-check-label">Approved</label>
        </div>
    </div>
    <div class="col-12">
        <button type="submit" class="btn btn-primary">Save</button>
        <a href="{{ route('admin.shops.index') }}" class="btn btn-secondary">Cancel</a>
    </div>
</form>
@endsection
