@extends('layouts.admin')

@section('title', 'Manage Payments')
@section('page-title', 'Order Payment Transactions')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 10px;">
    <form method="GET" action="{{ route('admin.payments.index') }}" style="display: flex; gap: 6px; flex-wrap: wrap;">
        <select name="status" class="form-select" style="width: 140px;">
            <option value="">All Statuses</option>
            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
            <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid</option>
            <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
            <option value="refunded" {{ request('status') === 'refunded' ? 'selected' : '' }}>Refunded</option>
        </select>

        <input type="text" name="search" class="form-control" placeholder="Search order / reference..." value="{{ request('search') }}" style="width: 220px;">
        <button type="submit" class="btn btn-secondary"><i class="bi bi-funnel"></i> Filter</button>
        @if(request()->hasAny(['status', 'search']))
            <a href="{{ route('admin.payments.index') }}" class="btn btn-secondary"><i class="bi bi-x-circle"></i> Reset</a>
        @endif
    </form>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="simple-table">
            <thead>
                <tr>
                    <th>Txn Ref</th>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>Method</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Paid Date</th>
                    <th style="text-align: right;">Update Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $payment)
                    <tr>
                        <td><code style="font-size: 11px;">{{ $payment->transaction_reference }}</code></td>
                        <td>
                            @if($payment->order)
                                <strong><a href="{{ route('admin.orders.show', $payment->order_id) }}">#{{ $payment->order->order_number }}</a></strong>
                            @else
                                <span style="color: var(--text-muted);">#{{ $payment->order_id }}</span>
                            @endif
                        </td>
                        <td>{{ $payment->order?->customer_name ?: '—' }}</td>
                        <td>{{ ucwords(str_replace('_', ' ', $payment->payment_method)) }}</td>
                        <td><strong>${{ number_format($payment->amount, 2) }}</strong></td>
                        <td>
                            @php
                                $badgeClass = match($payment->status) {
                                    'paid' => 'badge-success',
                                    'pending' => 'badge-pending',
                                    'refunded' => 'badge-unavailable',
                                    'failed' => 'badge-cancelled',
                                    default => 'badge-pending'
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }}">{{ ucfirst($payment->status) }}</span>
                        </td>
                        <td style="font-size: 12px; color: var(--text-muted);">
                            {{ $payment->paid_at ? $payment->paid_at->format('M d, Y H:i') : 'Unpaid' }}
                        </td>
                        <td style="text-align: right;">
                            <form method="POST" action="{{ route('admin.payments.update-status', $payment->id) }}" style="display: inline-flex; gap: 4px;">
                                @csrf
                                @method('PUT')
                                <select name="status" class="form-select" style="font-size: 12px; padding: 2px 6px;">
                                    <option value="paid" {{ $payment->status === 'paid' ? 'selected' : '' }}>Paid</option>
                                    <option value="pending" {{ $payment->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="failed" {{ $payment->status === 'failed' ? 'selected' : '' }}>Failed</option>
                                    <option value="refunded" {{ $payment->status === 'refunded' ? 'selected' : '' }}>Refunded</option>
                                </select>
                                <button type="submit" class="btn btn-secondary btn-sm" style="padding: 2px 6px;">Save</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 20px; color: var(--text-muted);">No payment records found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top: 16px;">
        {{ $payments->links() }}
    </div>
</div>
@endsection
