@extends('layouts.app')

@section('title', $dish->name . ' - Food Details')

@section('content')
<div style="margin-bottom: 16px;">
    <a href="{{ route('menu.index') }}" style="color: var(--text-muted); font-size: 13px;">
        <i class="bi bi-arrow-left"></i> Back to Food Menu
    </a>
</div>

<div class="card" style="margin-bottom: 24px;">
    <div class="grid grid-2" style="gap: 30px; align-items: start;">
        <!-- Large Food Image -->
        <div>
            <img src="{{ $dish->cover_image ?: 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=800' }}" 
                 alt="{{ $dish->name }}" 
                 style="width: 100%; height: 350px; object-fit: cover; border-radius: var(--radius); border: 1px solid var(--border);">
            
            @if(!empty($dish->images) && is_array($dish->images))
                <div style="display: flex; gap: 10px; margin-top: 10px; overflow-x: auto;">
                    @foreach($dish->images as $img)
                        <img src="{{ $img }}" alt="{{ $dish->name }}" style="width: 70px; height: 70px; object-fit: cover; border-radius: var(--radius-sm); border: 1px solid var(--border);">
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Dish Details & Actions -->
        <div>
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;">
                <div>
                    <span style="font-size: 12px; color: #b45309; font-weight: bold; text-transform: uppercase;">
                        {{ $dish->category->name }}
                    </span>
                    <h1 style="font-size: 24px; font-weight: 800; margin-top: 2px;">{{ $dish->name }}</h1>
                </div>

                @auth
                    <form method="POST" action="{{ route('favorites.toggle', $dish->id) }}">
                        @csrf
                        <button type="submit" class="btn btn-secondary btn-sm" title="Save to Favorites">
                            <i class="bi {{ $isFavorite ? 'bi-heart-fill' : 'bi-heart' }}" style="color: {{ $isFavorite ? '#e11d48' : 'inherit' }};"></i>
                            {{ $isFavorite ? 'Saved' : 'Favorite' }}
                        </button>
                    </form>
                @endauth
            </div>

            <!-- Rating & Prep Time -->
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px; font-size: 13px;">
                <span style="color: #b45309; font-weight: bold;">★ {{ number_format($dish->average_rating, 1) }}</span>
                <span style="color: var(--text-muted);">({{ $dish->reviews_count }} reviews)</span>
                <span>•</span>
                <span><i class="bi bi-clock"></i> {{ $dish->preparation_time }} mins prep</span>
                @if($dish->calories)
                    <span>•</span>
                    <span><i class="bi bi-fire"></i> {{ $dish->calories }} kcal</span>
                @endif
            </div>

            <!-- Price -->
            <div style="margin-bottom: 16px; display: flex; align-items: baseline; gap: 10px;">
                @if($dish->discount_price)
                    <span style="font-size: 26px; font-weight: 800; color: var(--primary);">${{ number_format($dish->discount_price, 2) }}</span>
                    <span style="text-decoration: line-through; color: var(--text-muted); font-size: 16px;">${{ number_format($dish->price, 2) }}</span>
                    <span class="badge badge-success">Save ${{ number_format($dish->price - $dish->discount_price, 2) }}</span>
                @else
                    <span style="font-size: 26px; font-weight: 800; color: var(--primary);">${{ number_format($dish->price, 2) }}</span>
                @endif
            </div>

            <!-- Description -->
            <p style="font-size: 14px; line-height: 1.6; color: var(--text-main); margin-bottom: 16px;">
                {{ $dish->description }}
            </p>

            <!-- Dietary Badges -->
            <div style="display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 16px;">
                @if($dish->is_chef_special)
                    <span class="badge badge-warning"><i class="bi bi-star-fill"></i> Chef's Signature Special</span>
                @endif
                @if($dish->is_vegetarian)
                    <span class="badge badge-success"><i class="bi bi-flower1"></i> 100% Vegetarian</span>
                @endif
                @if($dish->is_halal)
                    <span class="badge badge-info"><i class="bi bi-shield-check"></i> Halal Certified</span>
                @endif
                @if($dish->is_spicy)
                    <span class="badge badge-danger"><i class="bi bi-fire"></i> Spicy (Level {{ $dish->spicy_level ?: 2 }}/5)</span>
                @endif
            </div>

            <!-- Ingredients & Allergens Box -->
            <div style="background: var(--bg-page); padding: 12px 16px; border-radius: var(--radius); border: 1px solid var(--border); margin-bottom: 20px; font-size: 13px;">
                @if($dish->ingredients)
                    <div style="margin-bottom: 6px;">
                        <strong><i class="bi bi-egg"></i> Ingredients:</strong> {{ $dish->ingredients }}
                    </div>
                @endif
                @if($dish->allergens)
                    <div style="margin-bottom: 6px; color: #b91c1c;">
                        <strong><i class="bi bi-exclamation-circle"></i> Allergens:</strong> {{ $dish->allergens }}
                    </div>
                @endif
                @if($dish->dietary_info)
                    <div>
                        <strong><i class="bi bi-info-circle"></i> Dietary Info:</strong> {{ $dish->dietary_info }}
                    </div>
                @endif
            </div>

            <!-- Add to Cart Form -->
            @if($dish->is_available)
                <form method="POST" action="{{ route('cart.add') }}" style="background: #ffffff; padding: 14px; border: 1px solid var(--border); border-radius: var(--radius);">
                    @csrf
                    <input type="hidden" name="dish_id" value="{{ $dish->id }}">

                    <div style="display: flex; gap: 12px; align-items: flex-end; margin-bottom: 12px;">
                        <div style="width: 100px;">
                            <label for="quantity" style="display: block; font-size: 12px; font-weight: bold; margin-bottom: 4px;">Quantity</label>
                            <input type="number" id="quantity" name="quantity" class="form-control" value="1" min="1" max="20" required>
                        </div>
                        <div style="flex: 1;">
                            <label for="special_instructions" style="display: block; font-size: 12px; font-weight: bold; margin-bottom: 4px;">Special Cooking Request (Optional)</label>
                            <input type="text" id="special_instructions" name="special_instructions" class="form-control" placeholder="e.g. Less spicy, no onion, sauce on side...">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 10px; font-size: 15px; font-weight: bold;">
                        <i class="bi bi-cart-plus"></i> Add to Cart • ${{ number_format($dish->discount_price ?: $dish->price, 2) }}
                    </button>
                </form>
            @else
                <div class="alert alert-error" style="margin-top: 10px;">
                    <i class="bi bi-x-circle-fill"></i> <strong>Currently unavailable</strong> — Our kitchen has temporarily paused orders for this item today.
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Customer Reviews Section -->
<div class="card" style="margin-bottom: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; border-bottom: 1px solid var(--border); padding-bottom: 12px;">
        <h2 style="font-size: 18px; font-weight: bold;"><i class="bi bi-chat-left-text"></i> Customer Reviews & Ratings</h2>
        <div>
            <span style="font-size: 16px; font-weight: bold; color: #b45309;">★ {{ number_format($dish->average_rating, 1) }}</span>
            <span style="color: var(--text-muted); font-size: 13px;">({{ $dish->reviews_count }} customer ratings)</span>
        </div>
    </div>

    <!-- Review Form for Authenticated Customers -->
    @auth
        <div style="background: var(--bg-page); padding: 16px; border-radius: var(--radius); border: 1px solid var(--border); margin-bottom: 20px;">
            <h3 style="font-size: 14px; font-weight: bold; margin-bottom: 8px;"><i class="bi bi-star"></i> Leave a Review for {{ $dish->name }}</h3>
            <form method="POST" action="{{ route('reviews.store', $dish->id) }}">
                @csrf
                <div style="display: flex; gap: 12px; align-items: center; margin-bottom: 10px;">
                    <label for="rating" style="font-size: 13px; font-weight: 600;">Your Rating:</label>
                    <select name="rating" id="rating" class="form-control" style="width: 140px;" required>
                        <option value="5">5 Stars - Excellent</option>
                        <option value="4">4 Stars - Very Good</option>
                        <option value="3">3 Stars - Average</option>
                        <option value="2">2 Stars - Below Average</option>
                        <option value="1">1 Star - Poor</option>
                    </select>
                </div>
                <div class="form-group">
                    <textarea name="comment" class="form-control" rows="2" placeholder="Tell us how you enjoyed this dish..." required minlength="3"></textarea>
                </div>
                <button type="submit" class="btn btn-secondary btn-sm"><i class="bi bi-send"></i> Submit Review</button>
            </form>
        </div>
    @else
        <div style="margin-bottom: 20px; font-size: 13px; color: var(--text-muted);">
            <a href="{{ route('login') }}" style="font-weight: bold;">Log in</a> to submit a review for this dish.
        </div>
    @endauth

    <!-- Reviews List -->
    @if($dish->reviews->isEmpty())
        <p style="color: var(--text-muted); font-size: 13px; text-align: center; padding: 20px 0;">No reviews posted yet. Be the first to try and review this meal!</p>
    @else
        <div style="display: flex; flex-direction: column; gap: 12px;">
            @foreach($dish->reviews as $review)
                <div style="border-bottom: 1px solid var(--border); padding-bottom: 12px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                        <strong>{{ $review->user ? $review->user->name : 'Customer' }}</strong>
                        <span style="color: #b45309; font-weight: bold;">
                            @for($i = 1; $i <= $review->rating; $i++) ★ @endfor
                        </span>
                    </div>
                    <p style="font-size: 13px; color: var(--text-main); margin-bottom: 4px;">{{ $review->comment }}</p>
                    <small style="color: var(--text-muted);">Reviewed on {{ $review->created_at->format('M d, Y') }}</small>
                </div>
            @endforeach
        </div>
    @endif
</div>

<!-- Related Dishes -->
@if($relatedDishes->isNotEmpty())
    <div style="margin-top: 30px;">
        <h2 style="font-size: 18px; font-weight: bold; margin-bottom: 16px;">You Might Also Like from {{ $dish->category->name }}</h2>
        <div class="grid grid-4" style="gap: 16px;">
            @foreach($relatedDishes as $rel)
                <div class="dish-card">
                    <img src="{{ $rel->cover_image ?: 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=400' }}" alt="{{ $rel->name }}" style="height: 140px; object-fit: cover;">
                    <div class="dish-card-body">
                        <h4 style="font-size: 14px; font-weight: bold; margin-bottom: 4px;"><a href="{{ route('menu.show', $rel->slug) }}">{{ $rel->name }}</a></h4>
                        <div style="color: var(--primary); font-weight: bold; margin-bottom: 8px;">${{ number_format($rel->discount_price ?: $rel->price, 2) }}</div>
                        <a href="{{ route('menu.show', $rel->slug) }}" class="btn btn-secondary btn-sm" style="width: 100%; text-align: center;">View Dish</a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif
@endsection
