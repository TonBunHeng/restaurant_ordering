@extends('layouts.app')

@section('title', 'Shopping Cart - ' . \App\Models\RestaurantSetting::get('name', 'Restaurant'))

@section('content')
<div style="margin-bottom: 20px;">
    <h1 style="font-size: 22px; font-weight: 800;">Your Dining Cart</h1>
    <p style="font-size: 13px; color: var(--text-muted);">Review your selected food items before proceeding to checkout.</p>
</div>

@if(empty($cartItems))
    <div class="card" style="text-align: center; padding: 60px 20px;">
        <i class="bi bi-cart-x" style="font-size: 48px; color: var(--text-muted); display: block; margin-bottom: 12px;"></i>
        <h2 style="font-size: 18px; font-weight: bold; margin-bottom: 8px;">Your cart is currently empty</h2>
        <p style="color: var(--text-muted); font-size: 14px; margin-bottom: 20px;">Explore our delicious Cambodian menu and choose meals for dine-in, takeaway, or delivery.</p>
        <a href="{{ route('menu.index') }}" class="btn btn-primary"><i class="bi bi-book"></i> Browse Menu Dishes</a>
    </div>
@else
    <div class="grid" style="grid-template-columns: 2fr 1fr; gap: 24px; align-items: start;">
        <!-- Cart Items List -->
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; border-bottom: 1px solid var(--border); padding-bottom: 12px;">
                <h2 style="font-size: 16px; font-weight: bold;">Items in Cart ({{ count($cartItems) }})</h2>
                <form method="POST" action="{{ route('cart.clear') }}" onsubmit="return confirm('Clear all items from your cart?');">
                    @csrf
                    <button type="submit" class="btn btn-secondary btn-sm" style="color: var(--danger);"><i class="bi bi-trash"></i> Clear Cart</button>
                </form>
            </div>

            <table class="table" style="vertical-align: middle;">
                <thead>
                    <tr>
                        <th>Dish</th>
                        <th>Price</th>
                        <th>Qty</th>
                        <th>Total</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($cartItems as $item)
                        <tr>
                            <td>
                                <div style="display: flex; gap: 12px; align-items: center;">
                                    <img src="{{ $item['dish']->cover_image ?: 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=100' }}" alt="{{ $item['dish']->name }}" style="width: 50px; height: 50px; object-fit: cover; border-radius: var(--radius);">
                                    <div>
                                        <strong style="font-size: 14px;"><a href="{{ route('menu.show', $item['dish']->slug) }}">{{ $item['dish']->name }}</a></strong>
                                        @if(!empty($item['special_instructions']))
                                            <div style="font-size: 11px; color: var(--text-muted);"><i class="bi bi-chat-text"></i> Note: {{ $item['special_instructions'] }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>${{ number_format($item['price'], 2) }}</td>
                            <td style="white-space: nowrap;">
                                <form method="POST" action="{{ route('cart.update') }}" style="display: inline-flex; align-items: center; gap: 4px;">
                                    @csrf
                                    <input type="hidden" name="dish_id" value="{{ $item['dish']->id }}">
                                    <input type="number" name="quantity" value="{{ $item['quantity'] }}" min="1" max="20" class="form-control" style="width: 55px; padding: 4px; text-align: center;">
                                    <button type="submit" class="btn btn-secondary btn-sm" title="Update Quantity"><i class="bi bi-arrow-repeat"></i></button>
                                </form>
                            </td>
                            <td><strong>${{ number_format($item['line_total'], 2) }}</strong></td>
                            <td>
                                <form method="POST" action="{{ route('cart.remove') }}">
                                    @csrf
                                    <input type="hidden" name="dish_id" value="{{ $item['dish']->id }}">
                                    <button type="submit" class="btn btn-secondary btn-sm" style="color: var(--danger); padding: 4px 8px;" title="Remove"><i class="bi bi-x-lg"></i></button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div style="margin-top: 16px;">
                <a href="{{ route('menu.index') }}" style="font-size: 13px;"><i class="bi bi-plus-circle"></i> Add more dishes from menu</a>
            </div>
        </div>

        <!-- Order Summary & Promo Box -->
        <div>
            <!-- Promo Code Card -->
            <div class="card" style="margin-bottom: 16px;">
                <h3 style="font-size: 14px; font-weight: bold; margin-bottom: 10px;"><i class="bi bi-tag-fill" style="color: var(--primary);"></i> Have a Promo Code?</h3>
                @if($promo)
                    <div style="display: flex; justify-content: space-between; align-items: center; background: #ecfdf5; padding: 10px 12px; border-radius: var(--radius); border: 1px solid #a7f3d0;">
                        <div>
                            <strong style="color: #065f46;">{{ $promo->code }}</strong>
                            <div style="font-size: 11px; color: #047857;">- ${{ number_format($discountAmount, 2) }} discount applied</div>
                        </div>
                        <form method="POST" action="{{ route('cart.promo.remove') }}" style="margin: 0;">
                            @csrf
                            <button type="submit" class="btn btn-secondary btn-sm" style="padding: 2px 6px; font-size: 11px;">Remove</button>
                        </form>
                    </div>
                @else
                    <form method="POST" action="{{ route('cart.promo') }}" style="display: flex; gap: 6px;">
                        @csrf
                        <input type="text" name="promo_code" class="form-control" placeholder="Enter promo code" style="text-transform: uppercase;" required>
                        <button type="submit" class="btn btn-secondary btn-sm">Apply</button>
                    </form>
                @endif
            </div>

            <!-- Price Breakdown Card -->
            <div class="card">
                <h3 style="font-size: 16px; font-weight: bold; margin-bottom: 16px; border-bottom: 1px solid var(--border); padding-bottom: 10px;">
                    Order Summary
                </h3>

                <div style="display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 14px;">
                    <span style="color: var(--text-muted);">Subtotal:</span>
                    <span>${{ number_format($subtotal, 2) }}</span>
                </div>

                @if($discountAmount > 0)
                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 14px; color: #059669;">
                        <span>Promo Discount:</span>
                        <span>- ${{ number_format($discountAmount, 2) }}</span>
                    </div>
                @endif

                @if($taxAmount > 0)
                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 14px;">
                        <span style="color: var(--text-muted);">Tax ({{ $taxPercent }}%):</span>
                        <span>+ ${{ number_format($taxAmount, 2) }}</span>
                    </div>
                @endif

                @if($serviceAmount > 0)
                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 14px;">
                        <span style="color: var(--text-muted);">Service Charge ({{ $servicePercent }}%):</span>
                        <span>+ ${{ number_format($serviceAmount, 2) }}</span>
                    </div>
                @endif

                <div style="display: flex; justify-content: space-between; margin-bottom: 12px; font-size: 14px;">
                    <span style="color: var(--text-muted);">Delivery Fee:</span>
                    <span>
                        @if($deliveryFee == 0)
                            <span style="color: #059669; font-weight: 600;">FREE</span>
                        @else
                            ${{ number_format($deliveryFee, 2) }}
                        @endif
                    </span>
                </div>

                <div style="display: flex; justify-content: space-between; font-size: 18px; font-weight: 800; border-top: 1px solid var(--border); padding-top: 12px; margin-bottom: 20px;">
                    <span>Grand Total:</span>
                    <span style="color: var(--primary);">${{ number_format($total, 2) }}</span>
                </div>

                @auth
                    <a href="{{ route('checkout.index') }}" class="btn btn-primary" style="width: 100%; padding: 12px; font-size: 15px; text-align: center;">
                        <i class="bi bi-credit-card"></i> Proceed to Checkout
                    </a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-primary" style="width: 100%; padding: 12px; font-size: 15px; text-align: center;">
                        <i class="bi bi-box-arrow-in-right"></i> Log in to Checkout
                    </a>
                @endauth
            </div>
        </div>
    </div>
@endif
@endsection
