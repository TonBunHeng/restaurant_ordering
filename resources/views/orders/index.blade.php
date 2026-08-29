@extends('layouts.app')

@section('title', 'My Orders - ' . \App\Models\RestaurantSetting::get('name', 'Restaurant'))

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <div>
        <h1 style="font-size: 22px; font-weight: 800;">My Order History</h1>
        <p style="font-size: 13px; color: var(--text-muted);">View status and receipts for all your past and active food orders.</p>
    </div>
    <a href="{{ route('menu.index') }}" class="btn btn-primary btn-sm"><i class="bi bi-cart-plus"></i> New Order</a>
</div>

@if($orders->isEmpty())
    <div class="card" style="text-align: center; padding: 60px 20px;">
        <i class="bi bi-receipt" style="font-size: 48px; color: var(--text-muted); display: block; margin-bottom: 12px;"></i>
        <h2 style="font-size: 18px; font-weight: bold; margin-bottom: 8px;">No orders found</h2>
        <p style="color: var(--text-muted); font-size: 14px; margin-bottom: 20px;">You haven't placed any food orders with us yet.</p>
        <a href="{{ route('menu.index') }}" class="btn btn-primary"><i class="bi bi-book"></i> Browse Menu</a>
    </div>
@else
    <div class="card">
        <table class="table">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Items</th>
                    <th>Total</th>
                    <th>Payment</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($orders as $order)
                    <tr>
                        <td>
                            <strong>{{ $order->order_number }}</strong>
                        </td>
                        <td>{{ $order->created_at->format('M d, Y H:i') }}</td>
                        <td>{{ $order->formatted_order_type }}</td>
                        <td>
                            <div style="max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; font-size: 12px;">
                                {{ $order->items->pluck('dish_name')->join(', ') }}
                            </div>
                        </td>
                        <td><strong>${{ number_format($order->total_amount, 2) }}</strong></td>
                        <td>
                            <span class="badge {{ $order->payment_status === 'paid' ? 'badge-success' : 'badge-warning' }}">
                                {{ ucfirst($order->payment_status) }}
                            </span>
                        </td>
                        <td>
                            @php
                                $badgeClass = match($order->order_status) {
                                    'completed', 'delivered' => 'badge-success',
                                    'cancelled' => 'badge-danger',
                                    'preparing', 'confirmed' => 'badge-warning',
                                    default => 'badge-info'
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }}">{{ ucfirst(str_replace('_', ' ', $order->order_status)) }}</span>
                        </td>
                        <td>
                            <a href="{{ route('orders.show', $order->order_number) }}" class="btn btn-secondary btn-sm">Receipt</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div style="margin-top: 16px;">
            {{ $orders->links() }}
        </div>
    </div>
@endif
@endsection
