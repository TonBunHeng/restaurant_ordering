@extends('layouts.admin')

@section('title', 'Promotions & Discounts')
@section('page-title', 'Promotions & Promo Codes Management')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 10px;">
    <form method="GET" action="{{ route('admin.promotions.index') }}" style="display: flex; gap: 6px;">
        <input type="text" name="search" class="form-control" placeholder="Search promo code..." value="{{ request('search') }}" style="width: 220px;">
        <button type="submit" class="btn btn-secondary"><i class="bi bi-search"></i></button>
    </form>
    <a href="{{ route('admin.promotions.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Add New Promo Code</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="simple-table">
            <thead>
                <tr>
                    <th>Promo Code</th>
                    <th>Discount</th>
                    <th>Min Order</th>
                    <th>Max Discount</th>
                    <th>Validity Period</th>
                    <th>Usage</th>
                    <th>Status</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($promotions as $promo)
                    <tr>
                        <td>
                            <strong style="font-size: 14px; color: var(--primary); font-family: monospace;">{{ $promo->code }}</strong>
                            @if($promo->name)
                                <div style="font-size: 11px; color: var(--text-muted);">{{ $promo->name }}</div>
                            @endif
                        </td>
                        <td>
                            <strong>{{ $promo->discount_type === 'percentage' ? $promo->discount_value . '%' : '$' . number_format($promo->discount_value, 2) }}</strong>
                        </td>
                        <td>${{ number_format($promo->min_order_amount, 2) }}</td>
                        <td>{{ $promo->max_discount_amount ? '$' . number_format($promo->max_discount_amount, 2) : 'No limit' }}</td>
                        <td style="font-size: 12px;">
                            {{ $promo->start_date ? $promo->start_date->format('M d, Y') : 'Immediate' }} — 
                            {{ $promo->end_date ? $promo->end_date->format('M d, Y') : 'No expiry' }}
                        </td>
                        <td>
                            {{ $promo->times_used }} {{ $promo->usage_limit ? '/ ' . $promo->usage_limit : 'uses' }}
                        </td>
                        <td>
                            <span class="badge {{ $promo->is_active ? 'badge-success' : 'badge-danger' }}">
                                {{ $promo->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td style="text-align: right;">
                            <div style="display: flex; gap: 4px; justify-content: flex-end;">
                                <a href="{{ route('admin.promotions.edit', $promo->id) }}" class="btn btn-secondary btn-sm"><i class="bi bi-pencil"></i> Edit</a>
                                <form method="POST" action="{{ route('admin.promotions.destroy', $promo->id) }}" onsubmit="return confirm('Delete promo code {{ $promo->code }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 20px; color: var(--text-muted);">No promotions found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top: 16px;">
        {{ $promotions->links() }}
    </div>
</div>
@endsection
