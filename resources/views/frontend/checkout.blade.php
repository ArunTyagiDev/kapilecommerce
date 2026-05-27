@extends('layouts.frontend')

@section('title', 'Checkout')

@section('content')
<h2>Checkout</h2>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5>Shipping Information</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('checkout.store') }}" method="POST" id="checkout-form">
                    @csrf

                    @if(Auth::check() && $userAddresses->count() > 0)
                    <div class="mb-3">
                        <label class="form-label">Use Saved Address</label>
                        <select class="form-select" id="saved-address-select">
                            <option value="">Select saved address</option>
                            @foreach($userAddresses as $address)
                                <option value="{{ $address->id }}" 
                                        data-name="{{ $address->name }}"
                                        data-email="{{ $address->email }}"
                                        data-phone="{{ $address->phone }}"
                                        data-address="{{ $address->address }}"
                                        data-city="{{ $address->city }}"
                                        data-state="{{ $address->state }}"
                                        data-pincode="{{ $address->pincode }}"
                                        data-country="{{ $address->country }}">
                                    {{ $address->name }} - {{ $address->address }}, {{ $address->city }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <hr>
                    @endif

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Name *</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', Auth::user()->name ?? '') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email *</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', Auth::user()->email ?? '') }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone *</label>
                        <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone') }}" required>
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="address" class="form-label">Address *</label>
                        <textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" rows="3" required>{{ old('address') }}</textarea>
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="city" class="form-label">City *</label>
                            <input type="text" class="form-control @error('city') is-invalid @enderror" id="city" name="city" value="{{ old('city') }}" required>
                            @error('city')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="state" class="form-label">State *</label>
                            <input type="text" class="form-control @error('state') is-invalid @enderror" id="state" name="state" value="{{ old('state') }}" required>
                            @error('state')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="pincode" class="form-label">Pincode *</label>
                            <input type="text" class="form-control @error('pincode') is-invalid @enderror" id="pincode" name="pincode" value="{{ old('pincode') }}" required>
                            @error('pincode')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="country" class="form-label">Country *</label>
                        <input type="text" class="form-control @error('country') is-invalid @enderror" id="country" name="country" value="{{ old('country', 'India') }}" required>
                        @error('country')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    @if(Auth::check())
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="save_address" name="save_address" value="1">
                            <label class="form-check-label" for="save_address">Save this address for future orders</label>
                        </div>
                    </div>
                    @endif

                    <hr>

                    <h5>Payment Method</h5>
                    <div class="mb-3">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="payment_method" id="phonepe" value="phonepe" required>
                            <label class="form-check-label" for="phonepe">
                                <i class="bi bi-phone"></i> Pay Using PhonePe
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="payment_method" id="paytm" value="paytm" required>
                            <label class="form-check-label" for="paytm">
                                <i class="bi bi-phone"></i> Pay Using Paytm
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="payment_method" id="google_pay" value="google_pay" required>
                            <label class="form-check-label" for="google_pay">
                                <i class="bi bi-google"></i> Pay Using Google Pay
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="payment_method" id="cod" value="cod" required>
                            <label class="form-check-label" for="cod">
                                <i class="bi bi-cash"></i> Cash on Delivery
                            </label>
                        </div>
                        @error('payment_method')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">Order Notes (Optional)</label>
                        <textarea class="form-control" id="notes" name="notes" rows="2">{{ old('notes') }}</textarea>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-100">Place Order</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5>Order Summary</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <h6>Items:</h6>
                    @foreach($cartItems as $item)
                    <div class="d-flex justify-content-between mb-2">
                        <div>
                            <small>{{ $item->product->name }}</small>
                            @if($item->variant)
                                <br><small class="text-muted">
                                    @foreach($item->variant->attributeValues as $value)
                                        <span class="badge bg-info">{{ $value->attribute->name }}: {{ $value->display_value ?? $value->value }}</span>
                                    @endforeach
                                </small>
                            @endif
                            <br><small class="text-muted">Qty: {{ $item->quantity }}</small>
                        </div>
                        <div>{{ format_inr($item->subtotal) }}</div>
                    </div>
                    @endforeach
                </div>

                <hr>

                <div class="d-flex justify-content-between mb-2">
                    <span>Subtotal:</span>
                    <span>{{ format_inr($subtotal) }}</span>
                </div>

                <div class="d-flex justify-content-between mb-2">
                    <span>Delivery Charges:</span>
                    <span>{{ format_inr($deliveryCharges) }}</span>
                </div>

                <hr>

                <div class="d-flex justify-content-between">
                    <strong>Total:</strong>
                    <strong>{{ format_inr($total) }}</strong>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('saved-address-select')?.addEventListener('change', function() {
        const option = this.options[this.selectedIndex];
        if (option.value) {
            document.getElementById('name').value = option.dataset.name;
            document.getElementById('email').value = option.dataset.email;
            document.getElementById('phone').value = option.dataset.phone;
            document.getElementById('address').value = option.dataset.address;
            document.getElementById('city').value = option.dataset.city;
            document.getElementById('state').value = option.dataset.state;
            document.getElementById('pincode').value = option.dataset.pincode;
            document.getElementById('country').value = option.dataset.country;
        }
    });
</script>
@endpush

