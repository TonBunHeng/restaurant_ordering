@extends('layouts.app')

@section('title', 'My Favorites - ' . \App\Models\RestaurantSetting::get('name', 'Restaurant'))

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <div>
        <h1 style="font-size: 22px; font-weight: 800;"><i class="bi bi-heart-fill" style="color: #e11d48;"></i> Saved Favorite Dishes</h1>
        <p style="font-size: 13px; color: var(--text-muted);">Quickly re-order meals you've saved to your favorites.</p>
    </div>
    <a href="{{ route('menu.index') }}" class="btn btn-secondary btn-sm"><i class="bi bi-book"></i> Menu</a>
</div>

@if($favorites->isEmpty())
    <div class="card" style="text-align: center; padding: 60px 20px;">
        <i class="bi bi-heart" style="font-size: 48px; color: var(--text-muted); display: block; margin-bottom: 12px;"></i>
        <h2 style="font-size: 18px; font-weight: bold; margin-bottom: 8px;">No favorite dishes saved</h2>
        <p style="color: var(--text-muted); font-size: 14px; margin-bottom: 20px;">Browse our menu and click the heart icon to save dishes for quick access.</p>
        <a href="{{ route('menu.index') }}" class="btn btn-primary"><i class="bi bi-book"></i> Explore Menu</a>
    </div>
@else
    <div class="grid grid-3">
        @foreach($favorites as $dish)
            <div class="dish-card">
                <img src="{{ $dish->cover_image ?: 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=600' }}" alt="{{ $dish->name }}">
                <div class="dish-card-body">
                    <span style="font-size: 11px; color: #b45309; font-weight: bold; text-transform: uppercase;">
                        {{ $dish->category->name }}
                    </span>
                    <h3 class="dish-card-title" style="margin: 4px 0 6px;">
                        <a href="{{ route('menu.show', $dish->slug) }}">{{ $dish->name }}</a>
                    </h3>
                    <p class="dish-card-desc">{{ $dish->short_description ?: Str::limit($dish->description, 75) }}</p>

                    <div class="dish-card-footer">
                        <div class="dish-price">${{ number_format($dish->discount_price ?: $dish->price, 2) }}</div>
                        <div style="display: flex; gap: 6px;">
                            <form method="POST" action="{{ route('cart.add') }}">
                                @csrf
                                <input type="hidden" name="dish_id" value="{{ $dish->id }}">
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-cart-plus"></i></button>
                            </form>
                            <form method="POST" action="{{ route('favorites.toggle', $dish->id) }}">
                                @csrf
                                <button type="submit" class="btn btn-secondary btn-sm" title="Remove from favorites" style="color: var(--danger);"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="pagination-wrapper" style="margin-top: 24px;">
        {{ $favorites->links() }}
    </div>
@endif
@endsection
