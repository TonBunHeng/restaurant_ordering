@extends('layouts.app')

@section('title', 'Food Menu - ' . \App\Models\RestaurantSetting::get('name', 'Restaurant'))

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
    <div>
        <h1 style="font-size: 22px; font-weight: 800;">Our Delicious Food Menu</h1>
        <p style="font-size: 13px; color: var(--text-muted);">Explore our traditional cuisine, chef specials, beverages, and desserts.</p>
    </div>

    <!-- Search & Sort Controls -->
    <form method="GET" action="{{ route('menu.index') }}" style="display: flex; gap: 8px; flex-wrap: wrap; align-items: center;">
        @if(request('category'))
            <input type="hidden" name="category" value="{{ request('category') }}">
        @endif
        @if(request('is_vegetarian'))
            <input type="hidden" name="is_vegetarian" value="1">
        @endif
        @if(request('is_spicy'))
            <input type="hidden" name="is_spicy" value="1">
        @endif
        @if(request('is_halal'))
            <input type="hidden" name="is_halal" value="1">
        @endif
        @if(request('is_chef_special'))
            <input type="hidden" name="is_chef_special" value="1">
        @endif

        <input type="text" name="search" class="form-control" placeholder="Search dish name or ingredient..." value="{{ request('search') }}" style="width: 220px;">
        
        <select name="sort" class="form-control" onchange="this.form.submit()" style="width: 140px;">
            <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Latest</option>
            <option value="popular" {{ request('sort') == 'popular' ? 'selected' : '' }}>Most Popular</option>
            <option value="rating" {{ request('sort') == 'rating' ? 'selected' : '' }}>Highest Rated</option>
            <option value="price_low" {{ request('sort') == 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
            <option value="price_high" {{ request('sort') == 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
        </select>

        <button type="submit" class="btn btn-secondary"><i class="bi bi-search"></i></button>
        @if(request()->hasAny(['search', 'category', 'is_vegetarian', 'is_spicy', 'is_halal', 'is_chef_special', 'sort']))
            <a href="{{ route('menu.index') }}" class="btn btn-secondary" title="Reset Filters"><i class="bi bi-x-circle"></i></a>
        @endif
    </form>
</div>

<!-- Category Filters Bar -->
<div style="display: flex; gap: 8px; overflow-x: auto; padding-bottom: 12px; margin-bottom: 16px;">
    <a href="{{ route('menu.index', request()->except('category', 'page')) }}" 
       class="btn {{ !request('category') ? 'btn-primary' : 'btn-secondary' }}" style="white-space: nowrap;">
        <i class="bi bi-grid"></i> All Categories
    </a>
    @foreach($categories as $cat)
        <a href="{{ route('menu.index', array_merge(request()->except('page'), ['category' => $cat->slug])) }}" 
           class="btn {{ request('category') == $cat->slug ? 'btn-primary' : 'btn-secondary' }}" style="white-space: nowrap;">
            {{ $cat->name }} ({{ $cat->dishes()->where('status', 'published')->count() }})
        </a>
    @endforeach
</div>

<!-- Quick Dietary Filter Checkboxes -->
<div style="display: flex; gap: 16px; margin-bottom: 20px; font-size: 13px; flex-wrap: wrap; background: #ffffff; padding: 10px 14px; border: 1px solid var(--border); border-radius: var(--radius);">
    <span style="font-weight: 600; color: #475569;"><i class="bi bi-funnel"></i> Filters:</span>
    <a href="{{ route('menu.index', array_merge(request()->all(), ['is_vegetarian' => request('is_vegetarian') ? null : 1])) }}" 
       style="text-decoration: none; color: {{ request('is_vegetarian') ? 'var(--primary)' : 'var(--text-main)' }}; font-weight: {{ request('is_vegetarian') ? 'bold' : 'normal' }};">
        <i class="bi {{ request('is_vegetarian') ? 'bi-check-square-fill text-primary' : 'bi-square' }}"></i> Vegetarian
    </a>
    <a href="{{ route('menu.index', array_merge(request()->all(), ['is_spicy' => request('is_spicy') ? null : 1])) }}" 
       style="text-decoration: none; color: {{ request('is_spicy') ? 'var(--primary)' : 'var(--text-main)' }}; font-weight: {{ request('is_spicy') ? 'bold' : 'normal' }};">
        <i class="bi {{ request('is_spicy') ? 'bi-check-square-fill text-primary' : 'bi-square' }}"></i> Spicy
    </a>
    <a href="{{ route('menu.index', array_merge(request()->all(), ['is_halal' => request('is_halal') ? null : 1])) }}" 
       style="text-decoration: none; color: {{ request('is_halal') ? 'var(--primary)' : 'var(--text-main)' }}; font-weight: {{ request('is_halal') ? 'bold' : 'normal' }};">
        <i class="bi {{ request('is_halal') ? 'bi-check-square-fill text-primary' : 'bi-square' }}"></i> Halal
    </a>
    <a href="{{ route('menu.index', array_merge(request()->all(), ['is_chef_special' => request('is_chef_special') ? null : 1])) }}" 
       style="text-decoration: none; color: {{ request('is_chef_special') ? 'var(--primary)' : 'var(--text-main)' }}; font-weight: {{ request('is_chef_special') ? 'bold' : 'normal' }};">
        <i class="bi {{ request('is_chef_special') ? 'bi-check-square-fill text-primary' : 'bi-square' }}"></i> Chef Special
    </a>
</div>

<!-- Dishes Grid -->
@if($dishes->isEmpty())
    <div class="card" style="text-align: center; padding: 40px;">
        <div style="font-size: 32px; color: var(--text-muted); margin-bottom: 8px;"><i class="bi bi-search"></i></div>
        <h3 style="font-size: 16px; margin-bottom: 8px;">No food items found</h3>
        <p style="color: var(--text-muted); font-size: 13px; margin-bottom: 16px;">Try adjusting your search terms or dietary filters.</p>
        <a href="{{ route('menu.index') }}" class="btn btn-primary">Clear all filters</a>
    </div>
@else
    <div class="grid grid-3">
        @foreach($dishes as $dish)
            <div class="dish-card">
                <div style="position: relative;">
                    <img src="{{ $dish->cover_image ?: 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=600' }}" alt="{{ $dish->name }}">
                    @if(auth()->check())
                        <form method="POST" action="{{ route('favorites.toggle', $dish->id) }}" style="position: absolute; top: 10px; right: 10px; margin: 0;">
                            @csrf
                            <button type="submit" style="border: none; background: rgba(255,255,255,0.85); width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; color: {{ auth()->user()->favorites()->where('dish_id', $dish->id)->exists() ? '#e11d48' : '#64748b' }};" title="Toggle Favorite">
                                <i class="bi {{ auth()->user()->favorites()->where('dish_id', $dish->id)->exists() ? 'bi-heart-fill' : 'bi-heart' }}"></i>
                            </button>
                        </form>
                    @endif
                </div>

                <div class="dish-card-body">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                        <span style="font-size: 11px; color: #b45309; font-weight: bold; text-transform: uppercase;">
                            {{ $dish->category->name }}
                        </span>
                        <div>
                            @if($dish->is_chef_special)
                                <span class="badge badge-warning" style="font-size: 9px;"><i class="bi bi-star-fill"></i> Special</span>
                            @endif
                            @if($dish->is_vegetarian)
                                <span class="badge badge-success" style="font-size: 9px;"><i class="bi bi-flower1"></i> Veg</span>
                            @endif
                            @if($dish->is_spicy)
                                <span class="badge badge-danger" style="font-size: 9px;"><i class="bi bi-fire"></i> Spicy</span>
                            @endif
                            @if($dish->is_halal)
                                <span class="badge badge-info" style="font-size: 9px;">Halal</span>
                            @endif
                        </div>
                    </div>

                    <h3 class="dish-card-title">
                        <a href="{{ route('menu.show', $dish->slug) }}">{{ $dish->name }}</a>
                    </h3>
                    <p class="dish-card-desc">{{ $dish->short_description ?: Str::limit($dish->description, 80) }}</p>

                    <div style="font-size: 12px; color: var(--text-muted); margin-bottom: 8px; display: flex; align-items: center; gap: 8px;">
                        <span style="color: #b45309;">★ {{ number_format($dish->average_rating, 1) }}</span>
                        <span>({{ $dish->reviews_count }} reviews)</span>
                        <span>• <i class="bi bi-clock"></i> {{ $dish->preparation_time }}m</span>
                    </div>

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

                        @if($dish->is_available)
                            <form method="POST" action="{{ route('cart.add') }}">
                                @csrf
                                <input type="hidden" name="dish_id" value="{{ $dish->id }}">
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-cart-plus"></i> Add to Cart</button>
                            </form>
                        @else
                            <span class="badge badge-danger" style="padding: 4px 8px;">Unavailable</span>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="pagination-wrapper" style="margin-top: 24px;">
        {{ $dishes->links() }}
    </div>
@endif
@endsection
