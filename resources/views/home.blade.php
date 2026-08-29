@extends('layouts.app')

@section('title', ($restaurant['name'] ?? 'Royal Khmer Kitchen') . ' — ' . ($restaurant['tagline'] ?? 'Traditional Restaurant Ordering & Table Reservations'))

@section('content')
<!-- Simple Hero / Welcome Banner -->
<div class="card" style="background-color: #0f2744; color: #ffffff; padding: 36px 28px; border: none; margin-bottom: 24px;">
    <div style="max-width: 700px;">
        <h1 style="font-size: 28px; font-weight: 800; margin-bottom: 8px;">Authentic Khmer Gastronomy & Fresh Steaks</h1>
        <p style="font-size: 14px; color: #cbd5e1; margin-bottom: 20px; line-height: 1.6;">
            {{ $restaurant['hero_subtitle'] ?? 'Order fresh meals directly to your doorstep or reserve a dining table online. Hand-crafted daily using local Cambodian herbs, fresh Kampot pepper, and premium meats.' }}
        </p>
        <div style="display: flex; gap: 12px; flex-wrap: wrap;">
            <a href="{{ route('menu.index') }}" class="btn btn-primary btn-lg" style="background-color: #2563eb; border-color: #2563eb;"><i class="bi bi-book"></i> Browse Food Menu</a>
            <a href="{{ route('reservations.create') }}" class="btn btn-secondary btn-lg" style="background-color: #ffffff; color: #0f2744;"><i class="bi bi-calendar-date"></i> Book a Table</a>
        </div>
    </div>
</div>

<!-- Quick Highlights Info Bar -->
<div class="grid grid-3" style="margin-bottom: 24px;">
    <div class="card" style="margin-bottom: 0;">
        <h3 style="font-size: 14px; font-weight: bold; margin-bottom: 4px;"><i class="bi bi-truck text-primary"></i> Doorstep Delivery</h3>
        <p style="font-size: 12px; color: var(--text-muted);">Free delivery on all food orders over ${{ number_format($restaurant['free_delivery_threshold'] ?? 30, 0) }}. Fresh and fast.</p>
    </div>
    <div class="card" style="margin-bottom: 0;">
        <h3 style="font-size: 14px; font-weight: bold; margin-bottom: 4px;"><i class="bi bi-grid-3x3-gap text-primary"></i> Table Reservations</h3>
        <p style="font-size: 12px; color: var(--text-muted);">{{ $availableTablesCount }} tables available today. Instant confirmation.</p>
    </div>
    <div class="card" style="margin-bottom: 0;">
        <h3 style="font-size: 14px; font-weight: bold; margin-bottom: 4px;"><i class="bi bi-flower1 text-success"></i> Fresh Ingredients</h3>
        <p style="font-size: 12px; color: var(--text-muted);">Authentic lemongrass kroeung, Tonle Sap fish, and Kampot peppercorns.</p>
    </div>
</div>

<!-- Menu Categories -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Food Categories</h2>
        <a href="{{ route('menu.index') }}" style="font-size: 13px; font-weight: 600;">View All Menu &rarr;</a>
    </div>
    <div class="grid grid-4">
        @foreach($categories as $category)
            <a href="{{ route('menu.index', ['category' => $category->slug]) }}" style="text-decoration: none; color: inherit;">
                <div style="border: 1px solid var(--border); border-radius: var(--radius); padding: 14px; text-align: center; background: #ffffff;">
                    <div style="font-size: 24px; margin-bottom: 6px; color: var(--primary);">
                        <i class="bi {{ $category->icon_class }}"></i>
                    </div>
                    <div style="font-weight: bold; font-size: 13px;">{{ $category->name }}</div>
                    <div style="font-size: 11px; color: var(--text-muted); margin-top: 2px;">{{ $category->published_dishes_count }} items</div>
                </div>
            </a>
        @endforeach
    </div>
</div>

<!-- Chef Recommended Dishes -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Chef Specials & Popular Dishes</h2>
        <a href="{{ route('menu.index') }}" style="font-size: 13px; font-weight: 600;">Full Menu &rarr;</a>
    </div>

    <div class="grid grid-3">
        @foreach($featuredDishes as $dish)
            <div class="dish-card">
                <img src="{{ $dish->cover_image ?: 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=600' }}" alt="{{ $dish->name }}">
                <div class="dish-card-body">
                    <div style="font-size: 11px; color: #b45309; font-weight: bold; text-transform: uppercase; margin-bottom: 2px;">
                        {{ $dish->category->name }}
                    </div>
                    <h3 class="dish-card-title">
                        <a href="{{ route('menu.show', $dish->slug) }}">{{ $dish->name }}</a>
                    </h3>
                    <p class="dish-card-desc">{{ $dish->short_description ?: Str::limit($dish->description, 80) }}</p>

                    <div class="dish-card-footer">
                        <div>
                            @if($dish->discount_price)
                                <span class="dish-price">${{ number_format($dish->discount_price, 2) }}</span>
                                <span style="text-decoration: line-through; font-size: 12px; color: var(--text-muted); margin-left: 4px;">
                                    ${{ number_format($dish->price, 2) }}
                                </span>
                            @else
                                <span class="dish-price">${{ number_format($dish->price, 2) }}</span>
                            @endif
                        </div>

                        <form method="POST" action="{{ route('cart.add') }}">
                            @csrf
                            <input type="hidden" name="dish_id" value="{{ $dish->id }}">
                            <input type="hidden" name="quantity" value="1">
                            <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-cart-plus"></i> Add to Cart</button>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
