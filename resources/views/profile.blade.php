@extends('layouts.app')

@section('title', 'My Profile & Account')

@section('content')
<div style="max-width: 900px; margin: 0 auto;">
    <div style="margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
        <div style="display: flex; align-items: center; gap: 16px;">
            @if($user->avatar)
                <img src="{{ $user->avatar }}" alt="{{ $user->name }}" style="width: 70px; height: 70px; border-radius: 50%; object-fit: cover; border: 2px solid var(--border);">
            @else
                <div style="width: 70px; height: 70px; border-radius: 50%; background: var(--primary-light); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 28px; font-weight: bold;">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
            @endif
            <div>
                <h1 style="font-size: 22px; font-weight: bold;">{{ $user->name }}</h1>
                <p style="color: var(--text-muted);">{{ $user->email }} • Member since {{ $user->created_at->format('M Y') }}</p>
            </div>
        </div>
        <div>
            <a href="{{ route('customer.dashboard') }}" class="btn btn-secondary btn-sm"><i class="bi bi-speedometer2"></i> Dashboard</a>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div style="display: flex; gap: 8px; border-bottom: 1px solid var(--border); margin-bottom: 20px;">
        <a href="{{ route('profile.show', ['tab' => 'profile']) }}" class="btn btn-sm {{ $tab === 'profile' ? 'btn-primary' : 'btn-secondary' }}">
            <i class="bi bi-person"></i> Profile Settings
        </a>
        <a href="{{ route('profile.show', ['tab' => 'orders']) }}" class="btn btn-sm {{ $tab === 'orders' ? 'btn-primary' : 'btn-secondary' }}">
            <i class="bi bi-receipt"></i> Orders ({{ $user->orders()->count() }})
        </a>
        <a href="{{ route('profile.show', ['tab' => 'reservations']) }}" class="btn btn-sm {{ $tab === 'reservations' ? 'btn-primary' : 'btn-secondary' }}">
            <i class="bi bi-calendar-check"></i> Bookings ({{ $user->reservations()->count() }})
        </a>
        <a href="{{ route('profile.show', ['tab' => 'favorites']) }}" class="btn btn-sm {{ $tab === 'favorites' ? 'btn-primary' : 'btn-secondary' }}">
            <i class="bi bi-heart"></i> Favorites ({{ $user->favorites()->count() }})
        </a>
        <a href="{{ route('profile.show', ['tab' => 'reviews']) }}" class="btn btn-sm {{ $tab === 'reviews' ? 'btn-primary' : 'btn-secondary' }}">
            <i class="bi bi-star"></i> Reviews ({{ $user->reviews()->count() }})
        </a>
    </div>

    @if($tab === 'profile')
        <div class="grid grid-2" style="gap: 20px;">
            <!-- Profile Details Form -->
            <div class="card">
                <h2 style="font-size: 16px; font-weight: bold; margin-bottom: 16px;"><i class="bi bi-pencil-square"></i> Edit Profile Information</h2>
                <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="form-group">
                        <label for="name">Full Name *</label>
                        <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input type="email" id="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="text" id="phone" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}" placeholder="+855 12 345 678">
                    </div>

                    <div class="form-group">
                        <label for="avatar">Profile Avatar Image (JPG, PNG, WebP max 2MB)</label>
                        <input type="file" id="avatar" name="avatar" class="form-control" accept="image/*">
                    </div>

                    <div class="form-group">
                        <label for="bio">Bio / Food Preferences</label>
                        <textarea id="bio" name="bio" class="form-control" rows="2" placeholder="e.g. Vegetarian, extra spicy preference...">{{ old('bio', $user->bio) }}</textarea>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%;">Save Changes</button>
                </form>
            </div>

            <!-- Password Change Form -->
            <div class="card">
                <h2 style="font-size: 16px; font-weight: bold; margin-bottom: 16px;"><i class="bi bi-shield-lock"></i> Change Password</h2>
                <form method="POST" action="{{ route('profile.password') }}">
                    @csrf
                    @method('PUT')

                    <div class="form-group">
                        <label for="current_password">Current Password *</label>
                        <input type="password" id="current_password" name="current_password" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="password">New Password *</label>
                        <input type="password" id="password" name="password" class="form-control" required minlength="6">
                    </div>

                    <div class="form-group">
                        <label for="password_confirmation">Confirm New Password *</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required minlength="6">
                    </div>

                    <button type="submit" class="btn btn-secondary" style="width: 100%; margin-top: 10px;">Update Password</button>
                </form>
            </div>
        </div>
    @elseif($tab === 'orders')
        <div class="card">
            <h2 style="font-size: 16px; font-weight: bold; margin-bottom: 16px;"><i class="bi bi-receipt"></i> Order History</h2>
            @if($orders->isEmpty())
                <p style="color: var(--text-muted);">No orders found.</p>
            @else
                <table class="table">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $order)
                            <tr>
                                <td><strong>{{ $order->order_number }}</strong></td>
                                <td>{{ $order->created_at->format('M d, Y H:i') }}</td>
                                <td>{{ $order->formatted_order_type }}</td>
                                <td><strong>${{ number_format($order->total_amount, 2) }}</strong></td>
                                <td><span class="badge badge-info">{{ ucfirst($order->order_status) }}</span></td>
                                <td><a href="{{ route('orders.show', $order->order_number) }}" class="btn btn-secondary btn-sm">Receipt</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div style="margin-top: 16px;">
                    {{ $orders->links() }}
                </div>
            @endif
        </div>
    @elseif($tab === 'reservations')
        <div class="card">
            <h2 style="font-size: 16px; font-weight: bold; margin-bottom: 16px;"><i class="bi bi-calendar-check"></i> Table Reservations</h2>
            @if($reservations->isEmpty())
                <p style="color: var(--text-muted);">No reservations found.</p>
            @else
                <table class="table">
                    <thead>
                        <tr>
                            <th>Booking #</th>
                            <th>Date & Time</th>
                            <th>Table</th>
                            <th>Guests</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reservations as $res)
                            <tr>
                                <td><strong>{{ $res->reservation_number }}</strong></td>
                                <td>{{ $res->reservation_date->format('M d, Y') }} at {{ $res->reservation_time }}</td>
                                <td>{{ $res->table ? $res->table->table_number : 'Standard' }} ({{ $res->table_type }})</td>
                                <td>{{ $res->guest_count }}</td>
                                <td><span class="badge {{ $res->status === 'confirmed' ? 'badge-success' : 'badge-warning' }}">{{ ucfirst($res->status) }}</span></td>
                                <td><a href="{{ route('reservations.show', $res->id) }}" class="btn btn-secondary btn-sm">View</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div style="margin-top: 16px;">
                    {{ $reservations->links() }}
                </div>
            @endif
        </div>
    @elseif($tab === 'favorites')
        <div class="card">
            <h2 style="font-size: 16px; font-weight: bold; margin-bottom: 16px;"><i class="bi bi-heart-fill" style="color:#e11d48;"></i> Saved Favorite Dishes</h2>
            @if($favorites->isEmpty())
                <p style="color: var(--text-muted);">You have not saved any dishes to your favorites yet.</p>
            @else
                <div class="grid grid-3" style="gap: 16px;">
                    @foreach($favorites as $dish)
                        <div class="dish-card" style="padding: 12px;">
                            <img src="{{ $dish->cover_image ?: asset('images/dish-placeholder.jpg') }}" alt="{{ $dish->name }}" style="width: 100%; height: 120px; object-fit: cover; border-radius: var(--radius); margin-bottom: 8px;">
                            <h3 style="font-size: 15px; font-weight: bold;"><a href="{{ route('menu.show', $dish->slug) }}">{{ $dish->name }}</a></h3>
                            <div style="color: var(--primary); font-weight: bold; margin: 4px 0;">${{ number_format($dish->discount_price ?: $dish->price, 2) }}</div>
                            <div style="display: flex; gap: 8px; margin-top: 8px;">
                                <form method="POST" action="{{ route('cart.add') }}" style="flex: 1;">
                                    @csrf
                                    <input type="hidden" name="dish_id" value="{{ $dish->id }}">
                                    <button type="submit" class="btn btn-primary btn-sm" style="width: 100%;"><i class="bi bi-cart-plus"></i> Order</button>
                                </form>
                                <form method="POST" action="{{ route('favorites.toggle', $dish->id) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-secondary btn-sm" title="Remove"><i class="bi bi-trash"></i></button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @elseif($tab === 'reviews')
        <div class="card">
            <h2 style="font-size: 16px; font-weight: bold; margin-bottom: 16px;"><i class="bi bi-star"></i> My Reviews & Ratings</h2>
            @if($reviews->isEmpty())
                <p style="color: var(--text-muted);">You haven't submitted any dish reviews yet.</p>
            @else
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    @foreach($reviews as $rev)
                        <div style="border: 1px solid var(--border); border-radius: var(--radius); padding: 12px;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                                <strong><a href="{{ route('menu.show', $rev->dish->slug) }}">{{ $rev->dish->name }}</a></strong>
                                <span style="color: #b45309; font-weight: bold;">
                                    @for($i=1; $i<=$rev->rating; $i++) ★ @endfor
                                </span>
                            </div>
                            <p style="font-size: 13px; margin-bottom: 4px;">"{{ $rev->comment }}"</p>
                            <small style="color: var(--text-muted);">Posted on {{ $rev->created_at->format('M d, Y') }}</small>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @endif
</div>
@endsection
