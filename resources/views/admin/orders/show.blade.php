@extends('layouts.admin')

@section('title', 'Process Order #' . $order->order_number . ' — Admin')
@section('page-title', 'Order Details & Status Update')

@section('content')
<div style="margin-bottom: 14px; display: flex; justify-content: space-between; align-items: center;">
    <a href="{{ route('admin.orders.index') }}" style="font-size: 13px;"><i class="bi bi-arrow-left"></i> Back to Orders List</a>
    <a href="{{ route('admin.orders.print', $order->id) }}" target="_blank" class="btn btn-secondary btn-sm"><i class="bi bi-printer"></i> Print Thermal Receipt</a>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 24px;">
    <!-- Left: Order Items & Delivery Info -->
    <div style="grid-column: span 2;">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="bi bi-receipt"></i> Order #{{ $order->order_number }}</h3>
                <span style="font-size: 12px; color: var(--text-muted);"><i class="bi bi-clock"></i> {{ $order->created_at->format('F d, Y \a\t h:i A') }}</span>
            </div>

            <div style="margin-bottom: 12px;">
                <span class="badge badge-info">{{ $order->formatted_order_type }}</span>
            </div>

            <div class="table-responsive">
                <table class="simple-table" style="margin-bottom: 16px;">
                    <thead>
                        <tr>
                            <th>Dish</th>
                            <th style="width: 100px; text-align: right;">Price</th>
                            <th style="width: 80px; text-align: center;">Qty</th>
                            <th style="width: 100px; text-align: right;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                            <tr>
                                <td>
                                    <strong>{{ $item->dish_name }}</strong>
                                    @if($item->special_instructions)
                                        <div style="font-size: 11px; color: #b45309;"><i class="bi bi-pencil-square"></i> {{ $item->special_instructions }}</div>
                                    @endif
                                </td>
                                <td style="text-align: right;">${{ number_format($item->unit_price, 2) }}</td>
                                <td style="text-align: center;">{{ $item->quantity }}</td>
                                <td style="text-align: right; font-weight: bold;">${{ number_format($item->subtotal_price, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div style="margin-left: auto; max-width: 280px; font-size: 13px; display: flex; flex-direction: column; gap: 4px;">
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-muted);">Subtotal:</span>
                    <span>${{ number_format($order->subtotal, 2) }}</span>
                </div>
                @if($order->discount_amount > 0)
                    <div style="display: flex; justify-content: space-between; color: #059669;">
                        <span>Discount ({{ $order->promo_code }}):</span>
                        <span>-${{ number_format($order->discount_amount, 2) }}</span>
                    </div>
                @endif
                @if($order->tax_amount > 0)
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: var(--text-muted);">Tax:</span>
                        <span>+${{ number_format($order->tax_amount, 2) }}</span>
                    </div>
                @endif
                @if($order->service_charge > 0)
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: var(--text-muted);">Service Charge:</span>
                        <span>+${{ number_format($order->service_charge, 2) }}</span>
                    </div>
                @endif
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-muted);">Delivery Fee:</span>
                    <span>${{ number_format($order->delivery_fee, 2) }}</span>
                </div>
                <hr style="border: none; border-top: 1px solid var(--border); margin: 6px 0;">
                <div style="display: flex; justify-content: space-between; font-size: 16px; font-weight: bold;">
                    <span>Total Amount:</span>
                    <span style="color: var(--primary);">${{ number_format($order->total_amount, 2) }}</span>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="bi bi-person-lines-fill"></i> Customer & Delivery Details</h3>
            </div>
            <div class="grid grid-2" style="font-size: 13px;">
                <div>
                    <div><strong>Customer Name:</strong> {{ $order->customer_name }}</div>
                    <div><strong>Phone:</strong> {{ $order->customer_phone }}</div>
                    <div><strong>Email:</strong> {{ $order->customer_email ?: 'N/A' }}</div>
                </div>
                <div>
                    <div><strong>Delivery / Table:</strong> {{ $order->delivery_address }}</div>
                    @if($order->notes)
                        <div style="margin-top: 4px; color: #b45309;"><strong>Special Notes:</strong> {{ $order->notes }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Right: Update Status Card -->
    <div>
        <div class="card" style="position: sticky; top: 20px;">
            <div class="card-header">
                <h3 class="card-title"><i class="bi bi-sliders"></i> Update Status</h3>
            </div>

            <form method="POST" action="{{ route('admin.orders.update-status', $order->id) }}">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label class="form-label" for="order_status">Kitchen / Order Status *</label>
                    <select id="order_status" name="order_status" class="form-select" required>
                        <option value="pending" {{ $order->order_status == 'pending' ? 'selected' : '' }}>Pending (Received)</option>
                        <option value="confirmed" {{ $order->order_status == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                        <option value="preparing" {{ $order->order_status == 'preparing' ? 'selected' : '' }}>Preparing in Kitchen</option>
                        <option value="ready" {{ $order->order_status == 'ready' ? 'selected' : '' }}>Ready for Delivery / Pickup</option>
                        <option value="out_for_delivery" {{ $order->order_status == 'out_for_delivery' ? 'selected' : '' }}>Out for Delivery</option>
                        <option value="completed" {{ in_array($order->order_status, ['completed', 'delivered']) ? 'selected' : '' }}>Completed / Served</option>
                        <option value="cancelled" {{ $order->order_status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="payment_status">Payment Status *</label>
                    <select id="payment_status" name="payment_status" class="form-select" required>
                        <option value="pending" {{ $order->payment_status == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="paid" {{ $order->payment_status == 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="refunded" {{ $order->payment_status == 'refunded' ? 'selected' : '' }}>Refunded</option>
                        <option value="failed" {{ $order->payment_status == 'failed' ? 'selected' : '' }}>Failed</option>
                    </select>
                </div>

                <div style="margin-top: 20px;">
                    <button type="submit" class="btn btn-primary" style="width: 100%;"><i class="bi bi-check-lg"></i> Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
