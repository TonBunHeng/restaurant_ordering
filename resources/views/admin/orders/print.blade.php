<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Print Receipt - Order #{{ $order->order_number }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
            font-size: 13px;
            color: #000;
            background: #fff;
            margin: 20px auto;
            max-width: 400px;
        }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { font-size: 18px; margin: 0 0 4px; }
        .details { margin-bottom: 15px; border-bottom: 1px dashed #000; padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th, td { padding: 5px 0; font-size: 12px; }
        th { text-align: left; border-bottom: 1px solid #000; }
        .text-right { text-align: right; }
        .totals { margin-top: 10px; border-top: 1px dashed #000; padding-top: 10px; }
        .totals-row { display: flex; justify-content: space-between; margin-bottom: 4px; }
        .grand-total { font-weight: bold; font-size: 16px; margin-top: 8px; border-top: 1px solid #000; padding-top: 6px; }
        .footer { text-align: center; margin-top: 25px; font-size: 11px; color: #555; }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="no-print" style="margin-bottom: 15px; text-align: right;">
        <button onclick="window.print()" style="padding: 6px 12px; font-weight: bold; cursor: pointer;">Print Now</button>
        <button onclick="window.close()" style="padding: 6px 12px; cursor: pointer;">Close</button>
    </div>

    <div class="header">
        <h1>{{ \App\Models\RestaurantSetting::get('name', 'Royal Khmer Kitchen') }}</h1>
        <div>{{ \App\Models\RestaurantSetting::get('address', 'Street 240, Daun Penh, Phnom Penh') }}</div>
        <div>Tel: {{ \App\Models\RestaurantSetting::get('phone', '+855 12 888 999') }}</div>
    </div>

    <div class="details">
        <div><strong>Order:</strong> #{{ $order->order_number }}</div>
        <div><strong>Date:</strong> {{ $order->created_at->format('d/m/Y H:i') }}</div>
        <div><strong>Type:</strong> {{ $order->formatted_order_type }}</div>
        <div><strong>Customer:</strong> {{ $order->customer_name }} ({{ $order->customer_phone }})</div>
        <div><strong>Address/Table:</strong> {{ $order->delivery_address }}</div>
        @if($order->notes)
            <div><strong>Note:</strong> {{ $order->notes }}</div>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th style="text-align: center;">Qty</th>
                <th class="text-right">Price</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
                <tr>
                    <td>{{ $item->dish_name }}</td>
                    <td style="text-align: center;">{{ $item->quantity }}</td>
                    <td class="text-right">${{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-right">${{ number_format($item->subtotal_price, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <div class="totals-row">
            <span>Subtotal:</span>
            <span>${{ number_format($order->subtotal, 2) }}</span>
        </div>
        @if($order->discount_amount > 0)
            <div class="totals-row">
                <span>Discount ({{ $order->promo_code }}):</span>
                <span>-${{ number_format($order->discount_amount, 2) }}</span>
            </div>
        @endif
        @if($order->tax_amount > 0)
            <div class="totals-row">
                <span>Tax:</span>
                <span>+${{ number_format($order->tax_amount, 2) }}</span>
            </div>
        @endif
        @if($order->service_charge > 0)
            <div class="totals-row">
                <span>Service Charge:</span>
                <span>+${{ number_format($order->service_charge, 2) }}</span>
            </div>
        @endif
        <div class="totals-row">
            <span>Delivery Fee:</span>
            <span>${{ number_format($order->delivery_fee, 2) }}</span>
        </div>
        <div class="totals-row grand-total">
            <span>GRAND TOTAL:</span>
            <span>${{ number_format($order->total_amount, 2) }}</span>
        </div>
        <div class="totals-row" style="margin-top: 6px; font-size: 11px;">
            <span>Payment ({{ strtoupper($order->payment_method) }}):</span>
            <span>{{ strtoupper($order->payment_status) }}</span>
        </div>
    </div>

    <div class="footer">
        Thank you for dining with us!<br>
        Please visit again soon.
    </div>

</body>
</html>
