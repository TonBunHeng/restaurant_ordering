@extends('layouts.app')

@section('title', 'Order Receipt #' . $order->order_number)

@section('content')
<div style="max-width: 800px; margin: 0 auto;">
    <div style="margin-bottom: 16px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
        <a href="{{ route('orders.index') }}" style="color: var(--text-muted); font-size: 13px;">
            <i class="bi bi-arrow-left"></i> Back to My Orders
        </a>
        <button onclick="window.print()" class="btn btn-secondary btn-sm"><i class="bi bi-printer"></i> Print Receipt</button>
    </div>

    <!-- Order Tracking Status Flow Indicator -->
    <div class="card" style="margin-bottom: 20px;">
        <h2 style="font-size: 15px; font-weight: bold; margin-bottom: 16px; display: flex; justify-content: space-between; align-items: center;">
            <span><i class="bi bi-geo-alt-fill"></i> Order Tracking</span>
            <span>Estimated Prep: <strong>~{{ $order->estimated_prep_time }} mins</strong></span>
        </h2>

        @if($order->order_status === 'cancelled')
            <div class="alert alert-error" style="margin: 0; text-align: center; font-weight: bold;">
                <i class="bi bi-x-circle-fill"></i> This order has been CANCELLED.
            </div>
        @else
            @php
                $statuses = ['pending', 'confirmed', 'preparing', 'ready', 'completed'];
                $currentIdx = array_search($order->order_status, $statuses);
                if ($currentIdx === false && $order->order_status === 'delivered') {
                    $currentIdx = 4;
                }
                if ($currentIdx === false && $order->order_status === 'out_for_delivery') {
                    $currentIdx = 3;
                }
            @endphp
            <div style="display: flex; justify-content: space-between; position: relative; margin: 20px 0;">
                <div style="position: absolute; top: 14px; left: 10%; right: 10%; height: 4px; background: var(--border); z-index: 1;"></div>
                <div style="position: absolute; top: 14px; left: 10%; width: {{ max(0, min(100, ($currentIdx / (count($statuses) - 1)) * 80)) }}%; height: 4px; background: var(--primary); z-index: 2;"></div>

                @foreach($statuses as $idx => $st)
                    <div style="text-align: center; position: relative; z-index: 3; flex: 1;">
                        <div style="width: 32px; height: 32px; border-radius: 50%; margin: 0 auto 6px; display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: bold; {{ $idx <= $currentIdx ? 'background: var(--primary); color: #fff;' : 'background: #e2e8f0; color: #64748b;' }}">
                            @if($idx < $currentIdx)
                                <i class="bi bi-check-lg"></i>
                            @else
                                {{ $idx + 1 }}
                            @endif
                        </div>
                        <div style="font-size: 12px; font-weight: {{ $idx === $currentIdx ? 'bold' : 'normal' }}; color: {{ $idx <= $currentIdx ? 'var(--text-main)' : 'var(--text-muted)' }}; text-transform: capitalize;">
                            {{ $st }}
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Order Receipt Card -->
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px dashed var(--border); padding-bottom: 16px; margin-bottom: 20px;">
            <div>
                <h1 style="font-size: 20px; font-weight: 800; margin-bottom: 4px;">Order #{{ $order->order_number }}</h1>
                <p style="font-size: 13px; color: var(--text-muted);">
                    Placed on {{ $order->created_at->format('l, M d, Y \a\t H:i') }}
                </p>
                <div style="margin-top: 6px;">
                    <span class="badge badge-info">{{ $order->formatted_order_type }}</span>
                </div>
            </div>
            <div style="text-align: right;">
                <div style="font-size: 12px; color: var(--text-muted);">Payment Status</div>
                <span class="badge {{ $order->payment_status === 'paid' ? 'badge-success' : 'badge-warning' }}" style="font-size: 12px;">
                    {{ strtoupper($order->payment_status) }}
                </span>
                <div style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">
                    Method: {{ ucwords(str_replace('_', ' ', $order->payment_method)) }}
                </div>
            </div>
        </div>

        <!-- Customer & Delivery Info -->
        <div style="background: var(--bg-page); padding: 12px 16px; border-radius: var(--radius); border: 1px solid var(--border); margin-bottom: 20px; font-size: 13px;">
            <div class="grid grid-2" style="gap: 10px;">
                <div>
                    <strong>Customer:</strong> {{ $order->customer_name }} ({{ $order->customer_phone }})<br>
                    @if($order->customer_email)
                        <strong>Email:</strong> {{ $order->customer_email }}<br>
                    @endif
                </div>
                <div>
                    <strong>Destination / Table:</strong> {{ $order->delivery_address }}<br>
                    @if($order->notes)
                        <strong>Notes:</strong> {{ $order->notes }}
                    @endif
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <table class="table" style="margin-bottom: 20px;">
            <thead>
                <tr>
                    <th>Item Description</th>
                    <th style="text-align: center;">Qty</th>
                    <th style="text-align: right;">Unit Price</th>
                    <th style="text-align: right;">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                    <tr>
                        <td>
                            <strong>{{ $item->dish_name }}</strong>
                            @if($item->special_instructions)
                                <div style="font-size: 11px; color: var(--text-muted);">Note: {{ $item->special_instructions }}</div>
                            @endif
                        </td>
                        <td style="text-align: center;">{{ $item->quantity }}</td>
                        <td style="text-align: right;">${{ number_format($item->unit_price, 2) }}</td>
                        <td style="text-align: right;">${{ number_format($item->subtotal_price, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals Calculation -->
        <div style="max-width: 320px; margin-left: auto; border-top: 1px solid var(--border); padding-top: 12px; font-size: 13px; display: flex; flex-direction: column; gap: 6px;">
            <div style="display: flex; justify-content: space-between;">
                <span style="color: var(--text-muted);">Subtotal:</span>
                <span>${{ number_format($order->subtotal, 2) }}</span>
            </div>

            @if($order->discount_amount > 0)
                <div style="display: flex; justify-content: space-between; color: #059669;">
                    <span>Discount ({{ $order->promo_code }}):</span>
                    <span>- ${{ number_format($order->discount_amount, 2) }}</span>
                </div>
            @endif

            @if($order->tax_amount > 0)
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-muted);">Tax:</span>
                    <span>+ ${{ number_format($order->tax_amount, 2) }}</span>
                </div>
            @endif

            @if($order->service_charge > 0)
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-muted);">Service Charge:</span>
                    <span>+ ${{ number_format($order->service_charge, 2) }}</span>
                </div>
            @endif

            <div style="display: flex; justify-content: space-between;">
                <span style="color: var(--text-muted);">Delivery Fee:</span>
                <span>${{ number_format($order->delivery_fee, 2) }}</span>
            </div>

            <div style="display: flex; justify-content: space-between; font-size: 18px; font-weight: 800; border-top: 1px solid var(--border); padding-top: 10px; margin-top: 6px;">
                <span>Grand Total:</span>
                <span style="color: var(--primary);">${{ number_format($order->total_amount, 2) }}</span>
            </div>
        </div>

        <!-- Order Cancellation Option if still Pending -->
        @if($order->order_status === 'pending')
            <div style="margin-top: 24px; padding-top: 16px; border-top: 1px solid var(--border); text-align: right;">
                <form method="POST" action="{{ route('orders.cancel', $order->id) }}" onsubmit="return confirm('Are you sure you want to cancel this pending order?');" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-secondary btn-sm" style="color: var(--danger);"><i class="bi bi-x-circle"></i> Cancel Order</button>
                </form>
            </div>
        @endif
    </div>
</div>
@endsection
