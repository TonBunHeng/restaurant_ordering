@extends('layouts.admin')

@section('title', 'Kitchen Order Management')
@section('page-title', 'Live Kitchen Order Display (KDS)')

@section('content')
<div style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
    <div>
        <p style="color: var(--text-muted); font-size: 13px;">Active meals queue in preparation. Orders are ordered chronologically.</p>
    </div>
    <div>
        <button onclick="window.location.reload()" class="btn btn-secondary btn-sm"><i class="bi bi-arrow-clockwise"></i> Refresh Queue</button>
    </div>
</div>

@if($activeOrders->isEmpty())
    <div class="card" style="text-align: center; padding: 60px 20px;">
        <i class="bi bi-check-circle" style="font-size: 48px; color: #15803d; display: block; margin-bottom: 12px;"></i>
        <h2 style="font-size: 18px; font-weight: bold; margin-bottom: 6px;">All Kitchen Orders Cleared!</h2>
        <p style="color: var(--text-muted); font-size: 14px;">No active orders pending in the kitchen right now.</p>
    </div>
@else
    <div class="grid grid-3" style="gap: 16px;">
        @foreach($activeOrders as $order)
            @php
                $cardBorder = match($order->order_status) {
                    'pending' => 'border-top: 4px solid #ef4444;',
                    'confirmed' => 'border-top: 4px solid #f59e0b;',
                    'preparing' => 'border-top: 4px solid #3b82f6;',
                    'ready' => 'border-top: 4px solid #10b981;',
                    default => 'border-top: 4px solid #6b7280;'
                };
            @endphp
            <div class="card" style="{{ $cardBorder }} padding: 16px; display: flex; flex-direction: column; justify-content: space-between;">
                <div>
                    <!-- Order Header -->
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px; border-bottom: 1px solid var(--border); padding-bottom: 8px;">
                        <div>
                            <strong style="font-size: 16px;">#{{ $order->order_number }}</strong>
                            <div style="font-size: 11px; color: var(--text-muted);"><i class="bi bi-clock"></i> {{ $order->created_at->diffForHumans() }} ({{ $order->created_at->format('H:i') }})</div>
                        </div>
                        <span class="badge {{ $order->order_status === 'ready' ? 'badge-success' : ($order->order_status === 'preparing' ? 'badge-info' : 'badge-warning') }}">
                            {{ strtoupper($order->order_status) }}
                        </span>
                    </div>

                    <!-- Customer & Dining info -->
                    <div style="font-size: 13px; margin-bottom: 12px; background: var(--bg-page); padding: 6px 10px; border-radius: var(--radius-sm);">
                        <strong>Customer:</strong> {{ $order->customer_name }}<br>
                        <strong>Type:</strong> <span style="color: var(--primary); font-weight: bold;">{{ $order->formatted_order_type }}</span>
                        @if($order->notes)
                            <div style="color: #b91c1c; font-weight: bold; margin-top: 4px;"><i class="bi bi-exclamation-triangle"></i> {{ $order->notes }}</div>
                        @endif
                    </div>

                    <!-- Items Checklist -->
                    <div style="margin-bottom: 16px;">
                        <strong style="font-size: 12px; text-transform: uppercase; color: var(--text-muted); display: block; margin-bottom: 6px;">Food Items:</strong>
                        <ul style="list-style: none; padding: 0; display: flex; flex-direction: column; gap: 8px;">
                            @foreach($order->items as $item)
                                <li style="font-size: 14px; display: flex; justify-content: space-between; border-bottom: 1px dashed var(--border); padding-bottom: 4px;">
                                    <span><strong>{{ $item->quantity }}x</strong> {{ $item->dish_name }}</span>
                                    @if($item->special_instructions)
                                        <small style="color: #b45309; font-weight: bold;">({{ $item->special_instructions }})</small>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <!-- Status Progression Action Buttons -->
                <div style="border-top: 1px solid var(--border); padding-top: 10px; display: flex; gap: 6px;">
                    @if($order->order_status === 'pending')
                        <form method="POST" action="{{ route('admin.kitchen.update-status', $order->id) }}" style="flex: 1;">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="order_status" value="confirmed">
                            <button type="submit" class="btn btn-primary btn-sm" style="width: 100%;"><i class="bi bi-check2"></i> Confirm</button>
                        </form>
                    @elseif($order->order_status === 'confirmed')
                        <form method="POST" action="{{ route('admin.kitchen.update-status', $order->id) }}" style="flex: 1;">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="order_status" value="preparing">
                            <button type="submit" class="btn btn-primary btn-sm" style="width: 100%; background: #2563eb;"><i class="bi bi-fire"></i> Start Preparing</button>
                        </form>
                    @elseif($order->order_status === 'preparing')
                        <form method="POST" action="{{ route('admin.kitchen.update-status', $order->id) }}" style="flex: 1;">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="order_status" value="ready">
                            <button type="submit" class="btn btn-primary btn-sm" style="width: 100%; background: #16a34a;"><i class="bi bi-bell"></i> Mark Ready</button>
                        </form>
                    @elseif($order->order_status === 'ready')
                        <form method="POST" action="{{ route('admin.kitchen.update-status', $order->id) }}" style="flex: 1;">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="order_status" value="completed">
                            <button type="submit" class="btn btn-secondary btn-sm" style="width: 100%;"><i class="bi bi-check-all"></i> Complete / Served</button>
                        </form>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
@endif
@endsection
