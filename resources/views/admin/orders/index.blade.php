@extends('layouts.admin')

@section('title', 'Manage Customer Orders — Admin')
@section('page-title', 'Customer Orders & Kitchen Queue')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 10px;">
    <!-- Filter & Search Form -->
    <form method="GET" action="{{ route('admin.orders.index') }}" style="display: flex; gap: 6px; flex-wrap: wrap;">
        <input type="text" name="search" class="form-control" placeholder="Search order #, name, phone..." value="{{ request('search') }}" style="width: 220px;">
        <select name="status" class="form-select" style="width: 150px;">
            <option value="">All Statuses</option>
            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
            <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
            <option value="preparing" {{ request('status') == 'preparing' ? 'selected' : '' }}>Preparing</option>
            <option value="ready" {{ request('status') == 'ready' ? 'selected' : '' }}>Ready for Delivery</option>
            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
        </select>
        <button type="submit" class="btn btn-secondary"><i class="bi bi-funnel"></i> Filter</button>
        @if(request()->hasAny(['search', 'status']))
            <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary"><i class="bi bi-x-circle"></i> Reset</a>
        @endif
    </form>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="simple-table">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Customer Details</th>
                    <th>Items</th>
                    <th>Total</th>
                    <th>Payment</th>
                    <th>Order Status</th>
                    <th style="text-align: right; width: 120px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                    <tr>
                        <td>
                            <strong><a href="{{ route('admin.orders.show', $order->id) }}"><i class="bi bi-receipt"></i> {{ $order->order_number }}</a></strong>
                            <div style="font-size: 11px; color: var(--text-muted);">{{ $order->created_at->format('M d, h:i A') }}</div>
                        </td>
                        <td>
                            <strong>{{ $order->customer_name }}</strong>
                            <div style="font-size: 11px; color: var(--text-muted);"><i class="bi bi-telephone"></i> {{ $order->customer_phone }}</div>
                        </td>
                        <td>
                            @foreach($order->items->take(2) as $item)
                                <div style="font-size: 12px;">{{ $item->quantity }}x {{ $item->dish_name }}</div>
                            @endforeach
                            @if($order->items->count() > 2)
                                <div style="font-size: 11px; color: var(--text-muted);">+{{ $order->items->count() - 2 }} more items...</div>
                            @endif
                        </td>
                        <td>
                            <strong style="color: var(--primary);">${{ number_format($order->total_amount, 2) }}</strong>
                        </td>
                        <td>
                            <span style="font-size: 12px; text-transform: uppercase;">{{ str_replace('_', ' ', $order->payment_method) }}</span>
                            <div>
                                <span class="badge badge-{{ $order->payment_status == 'paid' ? 'confirmed' : 'pending' }}" style="font-size: 9px;">
                                    {{ ucfirst($order->payment_status) }}
                                </span>
                            </div>
                        </td>
                        <td>
                            @php
                                $badgeClass = match($order->order_status) {
                                    'pending' => 'badge-pending',
                                    'confirmed', 'ready' => 'badge-confirmed',
                                    'preparing' => 'badge-preparing',
                                    'completed', 'delivered' => 'badge-completed',
                                    'cancelled' => 'badge-cancelled',
                                    default => 'badge-pending'
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }}">{{ ucfirst($order->order_status) }}</span>
                        </td>
                        <td style="text-align: right;">
                            <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-secondary btn-sm"><i class="bi bi-sliders"></i> Process</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colSpan="7" style="text-align: center; padding: 20px; color: var(--text-muted);">No orders found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 18px; flex-wrap: wrap; gap: 10px; font-size: 13px; color: var(--text-muted);">
        <div>
            Showing <strong>{{ $orders->firstItem() ?? 0 }}</strong> to <strong>{{ $orders->lastItem() ?? 0 }}</strong> of <strong>{{ $orders->total() }}</strong> orders
        </div>
        <div>
            {{ $orders->links() }}
        </div>
    </div>
</div>
@endsection
