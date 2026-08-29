@extends('layouts.admin')

@section('title', 'Customer Details - ' . $customer->name)
@section('page-title', 'Customer Profile & Activity: ' . $customer->name)

@section('content')
<div style="margin-bottom: 16px;">
    <a href="{{ route('admin.customers.index') }}" style="color: var(--text-muted); font-size: 13px;">
        <i class="bi bi-arrow-left"></i> Back to Customers Directory
    </a>
</div>

<div class="grid grid-3" style="gap: 20px; align-items: start; margin-bottom: 24px;">
    <!-- Customer Info Card -->
    <div class="card">
        <h2 style="font-size: 16px; font-weight: bold; margin-bottom: 12px; border-bottom: 1px solid var(--border); padding-bottom: 6px;">Customer Details</h2>
        <div style="font-size: 13px; display: flex; flex-direction: column; gap: 8px;">
            <div><strong>Name:</strong> {{ $customer->name }}</div>
            <div><strong>Email:</strong> {{ $customer->email }}</div>
            <div><strong>Phone:</strong> {{ $customer->phone ?: 'None' }}</div>
            <div><strong>Status:</strong> <span class="badge {{ $customer->status === 'active' ? 'badge-success' : 'badge-danger' }}">{{ ucfirst($customer->status) }}</span></div>
            <div><strong>Joined:</strong> {{ $customer->created_at->format('M d, Y') }}</div>
            @if($customer->bio)
                <div><strong>Preferences:</strong> {{ $customer->bio }}</div>
            @endif
        </div>
    </div>

    <!-- Lifetime Summary -->
    <div class="card">
        <h2 style="font-size: 16px; font-weight: bold; margin-bottom: 12px; border-bottom: 1px solid var(--border); padding-bottom: 6px;">Lifetime Statistics</h2>
        <div style="font-size: 13px; display: flex; flex-direction: column; gap: 8px;">
            <div><strong>Total Orders:</strong> {{ $customer->orders_count }}</div>
            <div><strong>Total Bookings:</strong> {{ $customer->reservations_count }}</div>
            <div><strong>Total Spent:</strong> ${{ number_format($customer->orders()->where('payment_status', 'paid')->sum('total_amount'), 2) }}</div>
            <div><strong>Reviews Left:</strong> {{ $customer->reviews_count }}</div>
        </div>
    </div>
</div>

<!-- Order History & Reservation History -->
<div class="grid grid-2" style="gap: 20px;">
    <div class="card">
        <h3 style="font-size: 16px; font-weight: bold; margin-bottom: 12px;"><i class="bi bi-receipt"></i> Recent Orders</h3>
        @if($orders->isEmpty())
            <p style="color: var(--text-muted); font-size: 13px;">No orders found.</p>
        @else
            <table class="simple-table">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Type</th>
                        <th>Total</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orders as $ord)
                        <tr>
                            <td><strong><a href="{{ route('admin.orders.show', $ord->id) }}">#{{ $ord->order_number }}</a></strong></td>
                            <td>{{ $ord->formatted_order_type }}</td>
                            <td>${{ number_format($ord->total_amount, 2) }}</td>
                            <td><span class="badge badge-info">{{ ucfirst($ord->order_status) }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div style="margin-top: 10px;">
                {{ $orders->links() }}
            </div>
        @endif
    </div>

    <div class="card">
        <h3 style="font-size: 16px; font-weight: bold; margin-bottom: 12px;"><i class="bi bi-calendar-check"></i> Recent Table Bookings</h3>
        @if($reservations->isEmpty())
            <p style="color: var(--text-muted); font-size: 13px;">No bookings found.</p>
        @else
            <table class="simple-table">
                <thead>
                    <tr>
                        <th>Booking #</th>
                        <th>Date & Time</th>
                        <th>Guests</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reservations as $res)
                        <tr>
                            <td><strong><a href="{{ route('admin.reservations.show', $res->id) }}">#{{ $res->reservation_number }}</a></strong></td>
                            <td>{{ $res->reservation_date->format('M d') }} at {{ $res->reservation_time }}</td>
                            <td>{{ $res->guest_count }}</td>
                            <td><span class="badge {{ $res->status === 'confirmed' ? 'badge-success' : 'badge-warning' }}">{{ ucfirst($res->status) }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div style="margin-top: 10px;">
                {{ $reservations->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
