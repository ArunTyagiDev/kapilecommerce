@extends('layouts.frontend')

@section('title', 'My Profile')

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
                </ul>
            </div>
        </div>
    </div>

    <div class="col-md-9">
        <h2>My Profile</h2>

        <div class="card mb-4">
            <div class="card-header">
                <h5>Personal Information</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('user.profile.update') }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="name" name="name" value="{{ $user->name }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="{{ $user->email }}" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Update Profile</button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5>Saved Addresses</h5>
                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addressModal">Add Address</button>
            </div>
            <div class="card-body">
                @forelse($addresses as $address)
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6>{{ $address->name }}</h6>
                                <p class="mb-1">{{ $address->address }}</p>
                                <p class="mb-1">{{ $address->city }}, {{ $address->state }} {{ $address->pincode }}</p>
                                <p class="mb-1">{{ $address->country }}</p>
                                <p class="mb-0">Phone: {{ $address->phone }}</p>
                                @if($address->is_default)
                                    <span class="badge bg-success">Default</span>
                                @endif
                            </div>
                            <div>
                                <form action="{{ route('user.address.delete', $address->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this address?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <p class="text-muted">No saved addresses</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Address Modal -->
<div class="modal fade" id="addressModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('user.address.save') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add Address</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name *</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email *</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone *</label>
                        <input type="text" class="form-control" name="phone" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Address *</label>
                        <textarea class="form-control" name="address" rows="2" required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">City *</label>
                            <input type="text" class="form-control" name="city" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">State *</label>
                            <input type="text" class="form-control" name="state" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Pincode *</label>
                            <input type="text" class="form-control" name="pincode" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Country *</label>
                            <input type="text" class="form-control" name="country" value="India" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_default" value="1" id="is_default">
                            <label class="form-check-label" for="is_default">Set as default address</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Address</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

