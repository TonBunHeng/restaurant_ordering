@extends('layouts.admin')

@section('title', 'Edit Promotion')
@section('page-title', 'Edit Promo Code #' . $promotion->code)

@section('content')
<div style="max-width: 700px;">
    <div class="card">
        <form method="POST" action="{{ route('admin.promotions.update', $promotion->id) }}">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="code">Promo Code *</label>
                <input type="text" id="code" name="code" class="form-control" value="{{ old('code', $promotion->code) }}" required style="text-transform: uppercase;">
            </div>

            <div class="form-group">
                <label for="name">Promotion Title</label>
                <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $promotion->name) }}">
            </div>

            <div class="grid grid-2" style="gap: 12px;">
                <div class="form-group">
                    <label for="discount_type">Discount Type *</label>
                    <select id="discount_type" name="discount_type" class="form-control" required>
                        <option value="percentage" {{ old('discount_type', $promotion->discount_type) === 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                        <option value="fixed" {{ old('discount_type', $promotion->discount_type) === 'fixed' ? 'selected' : '' }}>Fixed Amount ($)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="discount_value">Discount Value *</label>
                    <input type="number" step="0.01" id="discount_value" name="discount_value" class="form-control" value="{{ old('discount_value', $promotion->discount_value) }}" required>
                </div>
            </div>

            <div class="grid grid-2" style="gap: 12px;">
                <div class="form-group">
                    <label for="min_order_amount">Minimum Order Subtotal ($)</label>
                    <input type="number" step="0.01" id="min_order_amount" name="min_order_amount" class="form-control" value="{{ old('min_order_amount', $promotion->min_order_amount) }}">
                </div>

                <div class="form-group">
                    <label for="max_discount_amount">Maximum Discount Cap ($ - Optional)</label>
                    <input type="number" step="0.01" id="max_discount_amount" name="max_discount_amount" class="form-control" value="{{ old('max_discount_amount', $promotion->max_discount_amount) }}">
                </div>
            </div>

            <div class="grid grid-2" style="gap: 12px;">
                <div class="form-group">
                    <label for="start_date">Start Date</label>
                    <input type="date" id="start_date" name="start_date" class="form-control" value="{{ old('start_date', $promotion->start_date?->format('Y-m-d')) }}">
                </div>

                <div class="form-group">
                    <label for="end_date">End Date</label>
                    <input type="date" id="end_date" name="end_date" class="form-control" value="{{ old('end_date', $promotion->end_date?->format('Y-m-d')) }}">
                </div>
            </div>

            <div class="form-group">
                <label for="usage_limit">Maximum Usage Limit (Optional)</label>
                <input type="number" id="usage_limit" name="usage_limit" class="form-control" value="{{ old('usage_limit', $promotion->usage_limit) }}">
            </div>

            <div class="form-group">
                <label>
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $promotion->is_active) ? 'checked' : '' }}>
                    Active and ready for customer use
                </label>
            </div>

            <div style="display: flex; gap: 8px; margin-top: 20px;">
                <button type="submit" class="btn btn-primary">Update Promotion</button>
                <a href="{{ route('admin.promotions.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
