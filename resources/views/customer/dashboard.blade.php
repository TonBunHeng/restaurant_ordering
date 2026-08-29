@extends('layouts.app')

@section('title', 'My Customer Dashboard - ' . \App\Models\RestaurantSetting::get('name', 'Restaurant'))

@section('content')
<div style="margin-bottom: 24px;">
    <h1 style="font-size: 22px; font-weight: bold; margin-bottom: 4px;">Welcome back, {{ $user->name }}!</h1>
    <p style="color: var(--text-muted);">Manage your active food orders, upcoming table reservations, and favorite dishes.</p>
</div>

<!-- Active Orders Alert Banner (if any) -->
@if($activeOrders->isNotEmpty())
    <div class="card" style="margin-bottom: 20px; border-left: 4px solid var(--primary); background: #eff6ff;">
        <h2 style="font-size: 16px; font-weight: bold; margin-bottom: 10px; color: var(--primary);">
            <i class="bi bi-bell-fill"></i> Active Orders in Progress ({{ $activeOrders->count() }})
        </h2>
        <div style="display: flex; flex-direction: column; gap: 10px;">
            @foreach($activeOrders as $order)
                <div style="display: flex; justify-content: space-between; align-items: center; background: #fff; padding: 12px; border-radius: var(--radius); border: 1px solid var(--border); flex-wrap: wrap; gap: 10px;">
                    <div>
                        <strong>Order #{{ $order->order_number }}</strong> • 
                        <span class="badge badge-info">{{ ucfirst(str_replace('_', ' ', $order->order_status)) }}</span> • 
                        <span style="color: var(--text-muted);">{{ $order->formatted_order_type }}</span>
                        <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">
                            {{ $order->items->pluck('dish_name')->join(', ') }}
                        </div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <span style="font-weight: bold; font-size: 15px;">${{ number_format($order->total_amount, 2) }}</span>
                        <a href="{{ route('orders.show', $order->order_number) }}" class="btn btn-primary btn-sm"><i class="bi bi-geo-alt"></i> Track Order</a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif

<div class="grid grid-2" style="gap: 20px;">
    <!-- Recent Orders Column -->
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h2 style="font-size: 16px; font-weight: bold;"><i class="bi bi-receipt"></i> Recent Orders</h2>
            <a href="{{ route('orders.index') }}" class="btn btn-secondary btn-sm">View All</a>
        </div>

        @if($recentOrders->isEmpty())
            <div style="text-align: center; padding: 30px 10px; color: var(--text-muted);">
                <i class="bi bi-bag-x" style="font-size: 32px; display: block; margin-bottom: 8px;"></i>
                No orders placed yet.
                <div style="margin-top: 10px;">
                    <a href="{{ route('menu.index') }}" class="btn btn-primary btn-sm">Browse Menu</a>
                </div>
            </div>
        @else
            <table class="table" style="font-size: 13px;">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Type</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentOrders as $order)
                        <tr>
                            <td><strong>{{ $order->order_number }}</strong><br><small style="color: var(--text-muted);">{{ $order->created_at->format('M d, H:i') }}</small></td>
                            <td>{{ ucfirst(str_replace('_', ' ', $order->order_type)) }}</td>
                            <td>${{ number_format($order->total_amount, 2) }}</td>
                            <td>
                                @php
                                    $badgeClass = match($order->order_status) {
                                        'completed', 'delivered' => 'badge-success',
                                        'cancelled' => 'badge-danger',
                                        'preparing', 'confirmed' => 'badge-warning',
                                        default => 'badge-info'
                                    };
                                @endphp
                                <span class="badge {{ $badgeClass }}">{{ ucfirst($order->order_status) }}</span>
                            </td>
                            <td>
                                <a href="{{ route('orders.show', $order->order_number) }}" class="btn btn-secondary btn-sm" style="padding: 2px 6px;">Receipt</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <!-- Upcoming Table Reservations Column -->
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h2 style="font-size: 16px; font-weight: bold;"><i class="bi bi-calendar-check"></i> Upcoming Table Bookings</h2>
            <a href="{{ route('reservations.create') }}" class="btn btn-primary btn-sm">+ Book Table</a>
        </div>

        @if($upcomingReservations->isEmpty())
            <div style="text-align: center; padding: 30px 10px; color: var(--text-muted);">
                <i class="bi bi-calendar-x" style="font-size: 32px; display: block; margin-bottom: 8px;"></i>
                No upcoming table bookings.
                <div style="margin-top: 10px;">
                    <a href="{{ route('reservations.create') }}" class="btn btn-secondary btn-sm">Reserve a Table</a>
                </div>
            </div>
        @else
            <div style="display: flex; flex-direction: column; gap: 10px;">
                @foreach($upcomingReservations as $res)
                    <div style="border: 1px solid var(--border); border-radius: var(--radius); padding: 12px; display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <strong>{{ $res->table ? $res->table->table_number : 'Table Request' }}</strong> ({{ $res->guest_count }} Guests)
                            <div style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">
                                <i class="bi bi-clock"></i> {{ $res->reservation_date->format('l, M d, Y') }} at {{ $res->reservation_time }}
                            </div>
                        </div>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span class="badge {{ $res->status === 'confirmed' ? 'badge-success' : 'badge-warning' }}">{{ ucfirst($res->status) }}</span>
                            <a href="{{ route('reservations.show', $res->id) }}" class="btn btn-secondary btn-sm" style="padding: 2px 6px;">Details</a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

<!-- Favorites Section -->
<div class="card" style="margin-top: 20px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
        <h2 style="font-size: 16px; font-weight: bold;"><i class="bi bi-heart-fill" style="color: #e11d48;"></i> My Favorite Dishes</h2>
        <a href="{{ route('favorites.index') }}" class="btn btn-secondary btn-sm">View All ({{ $user->favorites()->count() }})</a>
    </div>

    @if($favoriteDishes->isEmpty())
        <p style="color: var(--text-muted); font-size: 13px;">You have not saved any dishes to your favorites yet. Click the heart icon on any menu item to save it here!</p>
    @else
        <div class="grid grid-3" style="gap: 16px;">
            @foreach($favoriteDishes as $dish)
                <div class="dish-card" style="display: flex; gap: 12px; align-items: center; padding: 10px;">
                    <img src="{{ $dish->cover_image ?: asset('images/dish-placeholder.jpg') }}" alt="{{ $dish->name }}" style="width: 60px; height: 60px; object-fit: cover; border-radius: var(--radius);">
                    <div style="flex: 1;">
                        <a href="{{ route('menu.show', $dish->slug) }}" style="font-weight: bold; color: var(--text-main); font-size: 14px;">{{ $dish->name }}</a>
                        <div style="color: var(--primary); font-weight: bold; margin-top: 2px;">
                            ${{ number_format($dish->discount_price ?: $dish->price, 2) }}
                        </div>
                    </div>
                    <form method="POST" action="{{ route('cart.add') }}">
                        @csrf
                        <input type="hidden" name="dish_id" value="{{ $dish->id }}">
                        <input type="hidden" name="quantity" value="1">
                        <button type="submit" class="btn btn-primary btn-sm" title="Add to Cart"><i class="bi bi-cart-plus"></i></button>
                    </form>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
