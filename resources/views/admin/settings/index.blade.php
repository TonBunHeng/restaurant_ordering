@extends('layouts.admin')

@section('title', 'Restaurant Business Settings')
@section('page-title', 'Restaurant Business & Operating Settings')

@section('content')
<div style="max-width: 800px;">
    <div class="card">
        <form method="POST" action="{{ route('admin.settings.update') }}">
            @csrf

            <h2 style="font-size: 16px; font-weight: bold; margin-bottom: 14px; border-bottom: 1px solid var(--border); padding-bottom: 8px;">
                <i class="bi bi-shop"></i> General Restaurant Profile
            </h2>

            <div class="grid grid-2" style="gap: 12px;">
                <div class="form-group">
                    <label for="name">Restaurant Display Name *</label>
                    <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $settings['name'] ?? '') }}" required>
                </div>

                <div class="form-group">
                    <label for="full_name">Legal / Full Business Name</label>
                    <input type="text" id="full_name" name="full_name" class="form-control" value="{{ old('full_name', $settings['full_name'] ?? '') }}">
                </div>
            </div>

            <div class="grid grid-2" style="gap: 12px;">
                <div class="form-group">
                    <label for="phone">Contact Phone / Hotline *</label>
                    <input type="text" id="phone" name="phone" class="form-control" value="{{ old('phone', $settings['phone'] ?? '') }}" required>
                </div>

                <div class="form-group">
                    <label for="email">Contact Email Address *</label>
                    <input type="email" id="email" name="email" class="form-control" value="{{ old('email', $settings['email'] ?? '') }}" required>
                </div>
            </div>

            <div class="form-group">
                <label for="address">Restaurant Physical Address *</label>
                <input type="text" id="address" name="address" class="form-control" value="{{ old('address', $settings['address'] ?? '') }}" required>
            </div>

            <div class="form-group">
                <label for="description">Tagline / Short Description</label>
                <textarea id="description" name="description" class="form-control" rows="2">{{ old('description', $settings['description'] ?? '') }}</textarea>
            </div>

            <h2 style="font-size: 16px; font-weight: bold; margin-top: 24px; margin-bottom: 14px; border-bottom: 1px solid var(--border); padding-bottom: 8px;">
                <i class="bi bi-clock"></i> Operating Hours & Reservations Policy
            </h2>

            <div class="grid grid-2" style="gap: 12px;">
                <div class="form-group">
                    <label for="opening_time">Opening Time * (e.g. 10:00)</label>
                    <input type="text" id="opening_time" name="opening_time" class="form-control" value="{{ old('opening_time', $settings['opening_time'] ?? '10:00') }}" required>
                </div>

                <div class="form-group">
                    <label for="closing_time">Closing Time * (e.g. 22:00)</label>
                    <input type="text" id="closing_time" name="closing_time" class="form-control" value="{{ old('closing_time', $settings['closing_time'] ?? '22:00') }}" required>
                </div>
            </div>

            <div class="grid grid-2" style="gap: 12px;">
                <div class="form-group">
                    <label for="reservation_duration">Standard Table Reservation Duration (Minutes) *</label>
                    <input type="number" id="reservation_duration" name="reservation_duration" class="form-control" value="{{ old('reservation_duration', $settings['reservation_duration'] ?? 120) }}" required>
                </div>

                <div class="form-group">
                    <label for="cancellation_window_hours">Cancellation Policy Window (Hours before booking) *</label>
                    <input type="number" id="cancellation_window_hours" name="cancellation_window_hours" class="form-control" value="{{ old('cancellation_window_hours', $settings['cancellation_window_hours'] ?? 2) }}" required>
                </div>
            </div>

            <h2 style="font-size: 16px; font-weight: bold; margin-top: 24px; margin-bottom: 14px; border-bottom: 1px solid var(--border); padding-bottom: 8px;">
                <i class="bi bi-currency-dollar"></i> Taxes, Service Charge & Delivery Pricing
            </h2>

            <div class="grid grid-3" style="gap: 12px;">
                <div class="form-group">
                    <label for="currency">Currency Symbol *</label>
                    <input type="text" id="currency" name="currency" class="form-control" value="{{ old('currency', $settings['currency'] ?? '$') }}" required>
                </div>

                <div class="form-group">
                    <label for="tax_percentage">Tax Percentage (%) *</label>
                    <input type="number" step="0.1" id="tax_percentage" name="tax_percentage" class="form-control" value="{{ old('tax_percentage', $settings['tax_percentage'] ?? 10) }}" required>
                </div>

                <div class="form-group">
                    <label for="service_charge_percentage">Service Charge (%) *</label>
                    <input type="number" step="0.1" id="service_charge_percentage" name="service_charge_percentage" class="form-control" value="{{ old('service_charge_percentage', $settings['service_charge_percentage'] ?? 5) }}" required>
                </div>
            </div>

            <div class="grid grid-3" style="gap: 12px;">
                <div class="form-group">
                    <label for="delivery_fee">Standard Delivery Fee ($) *</label>
                    <input type="number" step="0.01" id="delivery_fee" name="delivery_fee" class="form-control" value="{{ old('delivery_fee', $settings['delivery_fee'] ?? 2.00) }}" required>
                </div>

                <div class="form-group">
                    <label for="free_delivery_threshold">Free Delivery Threshold ($) *</label>
                    <input type="number" step="0.01" id="free_delivery_threshold" name="free_delivery_threshold" class="form-control" value="{{ old('free_delivery_threshold', $settings['free_delivery_threshold'] ?? 30.00) }}" required>
                </div>

                <div class="form-group">
                    <label for="min_delivery_order">Minimum Delivery Order ($) *</label>
                    <input type="number" step="0.01" id="min_delivery_order" name="min_delivery_order" class="form-control" value="{{ old('min_delivery_order', $settings['min_delivery_order'] ?? 10.00) }}" required>
                </div>
            </div>

            <div style="margin-top: 24px;">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> Save All Settings</button>
            </div>
        </form>
    </div>
</div>
@endsection
