@extends('layouts.admin')

@section('title', 'Payments')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Payments Management</h1>
    <a href="{{ route('admin.payments.export', request()->all()) }}" class="btn btn-success">
        <i class="bi bi-download"></i> Export to Excel
    </a>
</div>

<!-- Filters -->
<form method="GET" action="{{ route('admin.payments.index') }}" class="mb-4">
    <div class="row g-3">
        <div class="col-md-3">
            <input type="text" class="form-control" name="search" placeholder="Search transaction ID, order number..." value="{{ request('search') }}">
        </div>
        <div class="col-md-2">
            <select class="form-select" name="payment_method">
                <option value="">All Methods</option>
                <option value="phonepe" {{ request('payment_method') == 'phonepe' ? 'selected' : '' }}>PhonePe</option>
                <option value="paytm" {{ request('payment_method') == 'paytm' ? 'selected' : '' }}>Paytm</option>
                <option value="google_pay" {{ request('payment_method') == 'google_pay' ? 'selected' : '' }}>Google Pay</option>
                <option value="cod" {{ request('payment_method') == 'cod' ? 'selected' : '' }}>Cash on Delivery</option>
            </select>
        </div>
        <div class="col-md-2">
            <select class="form-select" name="status">
                <option value="">All Status</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="success" {{ request('status') == 'success' ? 'selected' : '' }}>Success</option>
                <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
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
                <th>ID</th>
                <th>Order Number</th>
                <th>Transaction ID</th>
                <th>Payment Method</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse($payments as $payment)
            <tr>
                <td>{{ $payment->id }}</td>
                <td>
                    <a href="{{ route('admin.orders.show', $payment->order_id) }}">
                        {{ $payment->order->order_number ?? 'N/A' }}
                    </a>
                </td>
                <td>{{ $payment->transaction_id ?? 'N/A' }}</td>
                <td><span class="badge bg-info">{{ $payment->payment_method_name }}</span></td>
                <td>{{ format_inr($payment->amount) }}</td>
                <td>
                    <span class="badge bg-{{ $payment->status === 'success' ? 'success' : ($payment->status === 'failed' ? 'danger' : 'warning') }}">
                        {{ ucfirst($payment->status) }}
                    </span>
                </td>
                <td>{{ $payment->created_at->format('M d, Y h:i A') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center">No payments found</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{ $payments->links() }}
@endsection

