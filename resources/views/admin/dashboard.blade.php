@extends('layouts.admin')

@section('title', 'Admin Dashboard — ' . \App\Models\RestaurantSetting::get('name', 'Restaurant'))
@section('page-title', 'Restaurant Operations Dashboard')

@section('content')
<!-- Today's Operations Overview Stats -->
<div class="grid grid-4" style="margin-bottom: 20px;">
    <div class="card" style="margin-bottom: 0;">
        <div style="font-size: 11px; font-weight: bold; color: var(--text-muted); text-transform: uppercase;">Today's Revenue</div>
        <div style="font-size: 24px; font-weight: 800; color: #15803d; margin: 4px 0;">${{ number_format($todaysRevenue, 2) }}</div>
        <div style="font-size: 11px; color: var(--text-muted);">From {{ $todaysOrdersCount }} orders placed today</div>
    </div>

    <div class="card" style="margin-bottom: 0;">
        <div style="font-size: 11px; font-weight: bold; color: var(--text-muted); text-transform: uppercase;">Kitchen & Pending Orders</div>
        <div style="font-size: 24px; font-weight: 800; color: var(--primary); margin: 4px 0;">{{ $activeKitchenOrdersCount }}</div>
        <div style="font-size: 11px; color: #b45309;"><a href="{{ route('admin.kitchen.index') }}">{{ $pendingOrdersCount }} pending review &rarr;</a></div>
    </div>

    <div class="card" style="margin-bottom: 0;">
        <div style="font-size: 11px; font-weight: bold; color: var(--text-muted); text-transform: uppercase;">Today's Table Bookings</div>
        <div style="font-size: 24px; font-weight: 800; color: var(--primary); margin: 4px 0;">{{ $todaysReservationsCount }}</div>
        <div style="font-size: 11px; color: var(--text-muted);">{{ $pendingReservationsCount }} pending confirmation</div>
    </div>

    <div class="card" style="margin-bottom: 0;">
        <div style="font-size: 11px; font-weight: bold; color: var(--text-muted); text-transform: uppercase;">Dining Tables Status</div>
        <div style="font-size: 24px; font-weight: 800; color: #15803d; margin: 4px 0;">
            {{ $availableTablesCount }} <span style="font-size: 14px; color: var(--text-muted);">/ {{ $totalTablesCount }} free</span>
        </div>
        <div style="font-size: 11px; color: var(--text-muted);"><a href="{{ route('admin.tables.map') }}">View floor map &rarr;</a></div>
    </div>
</div>

<div class="grid grid-2" style="margin-bottom: 20px;">
    <!-- Recent Orders Table -->
    <div class="card">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
            <h3 class="card-title" style="font-size: 15px; font-weight: bold;"><i class="bi bi-receipt"></i> Recent Orders</h3>
            <a href="{{ route('admin.orders.index') }}" style="font-size: 12px;">View All &rarr;</a>
        </div>

        @if($recentOrders->isEmpty())
            <p style="color: var(--text-muted); font-size: 13px;">No recent orders.</p>
        @else
            <div class="table-responsive">
                <table class="simple-table">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Customer</th>
                            <th>Total</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentOrders as $order)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.orders.show', $order->id) }}"><strong>{{ $order->order_number }}</strong></a>
                                </td>
                                <td>{{ $order->customer_name }}</td>
                                <td style="font-weight: bold;">${{ number_format($order->total_amount, 2) }}</td>
                                <td>
                                    <span class="badge badge-info">
                                        {{ ucfirst($order->order_status) }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- Recent Table Reservations Table -->
    <div class="card">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
            <h3 class="card-title" style="font-size: 15px; font-weight: bold;"><i class="bi bi-calendar-check"></i> Upcoming Table Bookings</h3>
            <a href="{{ route('admin.reservations.index') }}" style="font-size: 12px;">View All &rarr;</a>
        </div>

        @if($recentReservations->isEmpty())
            <p style="color: var(--text-muted); font-size: 13px;">No reservations.</p>
        @else
            <div class="table-responsive">
                <table class="simple-table">
                    <thead>
                        <tr>
                            <th>Table</th>
                            <th>Date & Time</th>
                            <th>Guest</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentReservations as $res)
                            <tr>
                                <td>
                                    <strong>{{ $res->table ? $res->table->table_number : ($res->table_type ?: 'Standard') }}</strong>
                                </td>
                                <td>{{ $res->reservation_date->format('M d') }} at {{ $res->reservation_time }}</td>
                                <td>{{ $res->guest_name }} ({{ $res->guest_count }}p)</td>
                                <td>
                                    <span class="badge {{ $res->status === 'confirmed' ? 'badge-success' : 'badge-pending' }}">
                                        {{ ucfirst($res->status) }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

<div class="grid grid-2">
    <!-- Top Selling Dishes -->
    <div class="card">
        <h3 style="font-size: 15px; font-weight: bold; margin-bottom: 12px;"><i class="bi bi-trophy"></i> Popular Dishes</h3>
        @if($popularDishes->isEmpty())
            <p style="color: var(--text-muted); font-size: 13px;">No dish sales recorded yet.</p>
        @else
            <ul style="list-style: none; padding: 0; display: flex; flex-direction: column; gap: 8px;">
                @foreach($popularDishes as $dish)
                    <li style="display: flex; justify-content: space-between; font-size: 13px; border-bottom: 1px dashed var(--border); padding-bottom: 4px;">
                        <span><strong>{{ $dish->dish_name }}</strong></span>
                        <span class="badge badge-success">{{ $dish->total_qty }} orders</span>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

    <!-- Recent Activity Audit Logs -->
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
            <h3 style="font-size: 15px; font-weight: bold;"><i class="bi bi-clock-history"></i> Recent Staff Actions</h3>
            <a href="{{ route('admin.activity-logs.index') }}" style="font-size: 12px;">View Log &rarr;</a>
        </div>

        @if($recentActivities->isEmpty())
            <p style="color: var(--text-muted); font-size: 13px;">No recent activity logs.</p>
        @else
            <ul style="list-style: none; padding: 0; display: flex; flex-direction: column; gap: 8px;">
                @foreach($recentActivities as $act)
                    <li style="font-size: 12px; border-bottom: 1px solid var(--border); padding-bottom: 4px;">
                        <strong>{{ $act->user ? $act->user->name : 'System' }}</strong>: {{ $act->description }}
                        <div style="font-size: 11px; color: var(--text-muted);">{{ $act->created_at->diffForHumans() }}</div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
@endsection
