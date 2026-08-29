@extends('layouts.app')

@section('title', 'Checkout Order - ' . \App\Models\RestaurantSetting::get('name', 'Restaurant'))

@section('content')
<div style="margin-bottom: 20px;">
    <h1 style="font-size: 22px; font-weight: 800;">Order Checkout</h1>
    <p style="font-size: 13px; color: var(--text-muted);">Please provide your dining details and choose your preferred payment option.</p>
</div>

<form method="POST" action="{{ route('checkout.process') }}">
    @csrf

    <div class="grid" style="grid-template-columns: 2fr 1fr; gap: 24px; align-items: start;">
        <!-- Left: Customer Details & Dining Type -->
        <div>
            <!-- Order Type Selection -->
            <div class="card" style="margin-bottom: 20px;">
                <h2 style="font-size: 16px; font-weight: bold; margin-bottom: 14px;">
                    <i class="bi bi-diagram-3"></i> 1. Select Dining Option
                </h2>

                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 16px;">
                    <label style="border: 2px solid var(--border); padding: 12px; border-radius: var(--radius); cursor: pointer; text-align: center; display: block;" id="label-delivery">
                        <input type="radio" name="order_type" value="delivery" checked onchange="toggleOrderType(this.value)" style="margin-bottom: 6px;">
                        <div style="font-weight: bold; font-size: 14px;"><i class="bi bi-truck"></i> Delivery</div>
                        <small style="color: var(--text-muted);">To your location</small>
                    </label>

                    <label style="border: 2px solid var(--border); padding: 12px; border-radius: var(--radius); cursor: pointer; text-align: center; display: block;" id="label-dinein">
                        <input type="radio" name="order_type" value="dine_in" onchange="toggleOrderType(this.value)" style="margin-bottom: 6px;">
                        <div style="font-weight: bold; font-size: 14px;"><i class="bi bi-shop"></i> Dine-in</div>
                        <small style="color: var(--text-muted);">At restaurant table</small>
                    </label>

                    <label style="border: 2px solid var(--border); padding: 12px; border-radius: var(--radius); cursor: pointer; text-align: center; display: block;" id="label-takeaway">
                        <input type="radio" name="order_type" value="takeaway" onchange="toggleOrderType(this.value)" style="margin-bottom: 6px;">
                        <div style="font-weight: bold; font-size: 14px;"><i class="bi bi-bag"></i> Takeaway</div>
                        <small style="color: var(--text-muted);">Pick up in person</small>
                    </label>
                </div>

                <!-- Dine-in Table Selection (Shown only if Dine-in selected) -->
                <div id="dinein-fields" style="display: none; background: var(--bg-page); padding: 14px; border-radius: var(--radius); border: 1px solid var(--border); margin-bottom: 12px;">
                    <label for="table_number" style="display: block; font-weight: bold; font-size: 13px; margin-bottom: 6px;">Select Table Number *</label>
                    <select name="table_number" id="table_number" class="form-control">
                        <option value="">-- Choose a table --</option>
                        @foreach($tables as $t)
                            <option value="{{ $t->table_number }}">{{ $t->table_number }} ({{ $t->location }} - {{ $t->capacity }} seats)</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Customer Contact Information -->
            <div class="card" style="margin-bottom: 20px;">
                <h2 style="font-size: 16px; font-weight: bold; margin-bottom: 14px;">
                    <i class="bi bi-person-lines-fill"></i> 2. Customer Contact Details
                </h2>

                <div class="grid grid-2" style="gap: 12px;">
                    <div class="form-group">
                        <label for="customer_name">Full Name *</label>
                        <input type="text" id="customer_name" name="customer_name" class="form-control" value="{{ old('customer_name', $user?->name) }}" required>
                    </div>

                    <div class="form-group">
                        <label for="customer_phone">Phone Number *</label>
                        <input type="text" id="customer_phone" name="customer_phone" class="form-control" value="{{ old('customer_phone', $user?->phone) }}" placeholder="+855 12 888 999" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="customer_email">Email Address</label>
                    <input type="email" id="customer_email" name="customer_email" class="form-control" value="{{ old('customer_email', $user?->email) }}">
                </div>

                <!-- Delivery Address Box (Only for delivery) -->
                <div id="delivery-fields">
                    <div class="form-group">
                        <label for="delivery_address">Delivery Address (Street, Building, Area) *</label>
                        <textarea id="delivery_address" name="delivery_address" class="form-control" rows="2" placeholder="e.g. House #12, Street 240, Daun Penh, Phnom Penh">{{ old('delivery_address') }}</textarea>
                    </div>
                </div>

                <div class="form-group">
                    <label for="notes">Kitchen & Order Notes (Optional)</label>
                    <textarea id="notes" name="notes" class="form-control" rows="2" placeholder="e.g. Extra napkins, please call upon arrival...">{{ old('notes') }}</textarea>
                </div>
            </div>

            <!-- Payment Method -->
            <div class="card">
                <h2 style="font-size: 16px; font-weight: bold; margin-bottom: 14px;">
                    <i class="bi bi-wallet2"></i> 3. Payment Method
                </h2>

                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <label style="border: 1px solid var(--border); padding: 12px; border-radius: var(--radius); cursor: pointer; display: flex; align-items: center; gap: 12px;">
                        <input type="radio" name="payment_method" value="cash_on_delivery" checked>
                        <div>
                            <strong><i class="bi bi-cash-stack"></i> Cash on Delivery / Pay at Counter</strong>
                            <div style="font-size: 12px; color: var(--text-muted);">Pay with cash when your meal arrives or at checkout.</div>
                        </div>
                    </label>

                    <label style="border: 1px solid var(--border); padding: 12px; border-radius: var(--radius); cursor: pointer; display: flex; align-items: center; gap: 12px;">
                        <input type="radio" name="payment_method" value="credit_card">
                        <div>
                            <strong><i class="bi bi-credit-card-2-front"></i> Credit / Debit Card (Demo Mock Payment)</strong>
                            <div style="font-size: 12px; color: var(--text-muted);">Simulated card processing for testing orders.</div>
                        </div>
                    </label>

                    <label style="border: 1px solid var(--border); padding: 12px; border-radius: var(--radius); cursor: pointer; display: flex; align-items: center; gap: 12px;">
                        <input type="radio" name="payment_method" value="qr_payment">
                        <div>
                            <strong><i class="bi bi-qr-code"></i> Online KHQR / Mobile Banking (Demo Mock)</strong>
                            <div style="font-size: 12px; color: var(--text-muted);">Simulated instant QR payment.</div>
                        </div>
                    </label>
                </div>
            </div>
        </div>

        <!-- Right: Order Summary Confirmation -->
        <div>
            <div class="card">
                <h3 style="font-size: 16px; font-weight: bold; margin-bottom: 14px; border-bottom: 1px solid var(--border); padding-bottom: 8px;">
                    Order Summary ({{ count($cartItems) }} items)
                </h3>

                <div style="display: flex; flex-direction: column; gap: 10px; margin-bottom: 16px; max-height: 240px; overflow-y: auto;">
                    @foreach($cartItems as $item)
                        <div style="display: flex; justify-content: space-between; font-size: 13px;">
                            <div>
                                <strong>{{ $item['dish']->name }}</strong> x {{ $item['quantity'] }}
                                @if(!empty($item['special_instructions']))
                                    <div style="font-size: 11px; color: var(--text-muted);">({{ $item['special_instructions'] }})</div>
                                @endif
                            </div>
                            <span style="font-weight: 600;">${{ number_format($item['line_total'], 2) }}</span>
                        </div>
                    @endforeach
                </div>

                <div style="border-top: 1px solid var(--border); padding-top: 12px; font-size: 13px; display: flex; flex-direction: column; gap: 6px;">
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: var(--text-muted);">Subtotal:</span>
                        <span>${{ number_format($subtotal, 2) }}</span>
                    </div>

                    @if($discountAmount > 0)
                        <div style="display: flex; justify-content: space-between; color: #059669;">
                            <span>Promo ({{ $promo?->code }}):</span>
                            <span>- ${{ number_format($discountAmount, 2) }}</span>
                        </div>
                    @endif

                    @if($taxAmount > 0)
                        <div style="display: flex; justify-content: space-between;">
                            <span style="color: var(--text-muted);">Tax ({{ $taxPercent }}%):</span>
                            <span>+ ${{ number_format($taxAmount, 2) }}</span>
                        </div>
                    @endif

                    @if($serviceAmount > 0)
                        <div style="display: flex; justify-content: space-between;">
                            <span style="color: var(--text-muted);">Service Charge ({{ $servicePercent }}%):</span>
                            <span>+ ${{ number_format($serviceAmount, 2) }}</span>
                        </div>
                    @endif

                    <div style="display: flex; justify-content: space-between;" id="delivery-fee-row">
                        <span style="color: var(--text-muted);">Delivery Fee:</span>
                        <span>
                            @if($deliveryFee == 0)
                                <span style="color: #059669; font-weight: bold;">FREE</span>
                            @else
                                ${{ number_format($deliveryFee, 2) }}
                            @endif
                        </span>
                    </div>

                    <div style="display: flex; justify-content: space-between; font-size: 17px; font-weight: 800; border-top: 1px solid var(--border); padding-top: 10px; margin-top: 6px;">
                        <span>Total:</span>
                        <span style="color: var(--primary);">${{ number_format($total, 2) }}</span>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 18px; padding: 12px; font-size: 15px; font-weight: bold;">
                    <i class="bi bi-check-circle-fill"></i> Place Order Now
                </button>
            </div>
        </div>
    </div>
</form>

<script>
    function toggleOrderType(type) {
        var dineinFields = document.getElementById('dinein-fields');
        var deliveryFields = document.getElementById('delivery-fields');
        var deliveryAddress = document.getElementById('delivery_address');
        var tableSelect = document.getElementById('table_number');

        if (type === 'dine_in') {
            dineinFields.style.display = 'block';
            deliveryFields.style.display = 'none';
            deliveryAddress.removeAttribute('required');
            tableSelect.setAttribute('required', 'required');
        } else if (type === 'delivery') {
            dineinFields.style.display = 'none';
            deliveryFields.style.display = 'block';
            deliveryAddress.setAttribute('required', 'required');
            tableSelect.removeAttribute('required');
        } else { // takeaway
            dineinFields.style.display = 'none';
            deliveryFields.style.display = 'none';
            deliveryAddress.removeAttribute('required');
            tableSelect.removeAttribute('required');
        }
    }
</script>
@endsection
