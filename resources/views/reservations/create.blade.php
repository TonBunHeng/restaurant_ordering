@extends('layouts.app')

@section('title', 'Book a Dining Table - ' . \App\Models\RestaurantSetting::get('name', 'Restaurant'))

@section('content')
<div style="max-width: 900px; margin: 0 auto;">
    <div style="margin-bottom: 20px;">
        <h1 style="font-size: 22px; font-weight: 800;">Reserve a Dining Table</h1>
        <p style="font-size: 13px; color: var(--text-muted);">
            Book in advance for family dining, private rooms, and special gatherings. Opening hours: {{ $openingTime }} - {{ $closingTime }}.
        </p>
    </div>

    <!-- Step 1: Filter Availability Criteria -->
    <div class="card" style="margin-bottom: 20px; background: #eff6ff; border-left: 4px solid var(--primary);">
        <h2 style="font-size: 15px; font-weight: bold; margin-bottom: 12px; color: var(--primary);">
            <i class="bi bi-funnel-fill"></i> 1. Check Date, Time & Party Size
        </h2>

        <form method="GET" action="{{ route('reservations.create') }}" style="display: flex; gap: 12px; flex-wrap: wrap; align-items: flex-end;">
            <div style="flex: 1; min-width: 160px;">
                <label for="filter_date" style="display: block; font-size: 12px; font-weight: bold; margin-bottom: 4px;">Date *</label>
                <input type="date" id="filter_date" name="date" class="form-control" value="{{ $date }}" min="{{ now()->format('Y-m-d') }}" required>
            </div>

            <div style="flex: 1; min-width: 140px;">
                <label for="filter_time" style="display: block; font-size: 12px; font-weight: bold; margin-bottom: 4px;">Time Slot *</label>
                <select id="filter_time" name="time" class="form-control" required>
                    @foreach($timeSlots as $slot)
                        <option value="{{ $slot }}" {{ $time === $slot ? 'selected' : '' }}>{{ $slot }}</option>
                    @endforeach
                </select>
            </div>

            <div style="width: 110px;">
                <label for="filter_guests" style="display: block; font-size: 12px; font-weight: bold; margin-bottom: 4px;">Guests *</label>
                <input type="number" id="filter_guests" name="guests" class="form-control" value="{{ $guests }}" min="1" max="50" required>
            </div>

            <button type="submit" class="btn btn-primary" style="height: 38px;">
                <i class="bi bi-search"></i> Check Availability
            </button>
        </form>
    </div>

    <!-- Step 2: Select Available Table & Complete Reservation -->
    <form method="POST" action="{{ route('reservations.store') }}">
        @csrf
        <input type="hidden" name="reservation_date" value="{{ $date }}">
        <input type="hidden" name="reservation_time" value="{{ $time }}">
        <input type="hidden" name="guest_count" value="{{ $guests }}">

        <div class="card" style="margin-bottom: 20px;">
            <h2 style="font-size: 16px; font-weight: bold; margin-bottom: 14px;">
                <i class="bi bi-grid-3x3"></i> 2. Choose an Available Table for {{ $guests }} Guests on {{ \Carbon\Carbon::parse($date)->format('M d, Y') }} at {{ $time }}
            </h2>

            @if($availableTables->isEmpty())
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle"></i> No tables with at least {{ $guests }} seats are available for <strong>{{ \Carbon\Carbon::parse($date)->format('M d, Y') }} at {{ $time }}</strong>. Please try another time slot or date.
                </div>
            @else
                <div class="grid grid-3" style="gap: 12px; margin-bottom: 16px;">
                    @foreach($availableTables as $table)
                        <label style="border: 2px solid var(--border); padding: 14px; border-radius: var(--radius); cursor: pointer; display: block; background: #ffffff;">
                            <input type="radio" name="table_id" value="{{ $table->id }}" required {{ $loop->first ? 'checked' : '' }}>
                            <div style="font-weight: bold; font-size: 15px; margin-top: 4px;">{{ $table->table_number }}</div>
                            <div style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">
                                <i class="bi bi-people"></i> Capacity: <strong>{{ $table->capacity }} Seats</strong>
                            </div>
                            <div style="font-size: 12px; color: #b45309; margin-top: 2px;">
                                <i class="bi bi-geo-alt"></i> Location: {{ $table->location }}
                            </div>
                            @if($table->description)
                                <div style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">{{ $table->description }}</div>
                            @endif
                        </label>
                    @endforeach
                </div>
            @endif
        </div>

        @if($availableTables->isNotEmpty())
            <!-- Step 3: Guest Details -->
            <div class="card">
                <h2 style="font-size: 16px; font-weight: bold; margin-bottom: 14px;">
                    <i class="bi bi-person-badge"></i> 3. Guest Information
                </h2>

                <div class="grid grid-2" style="gap: 12px;">
                    <div class="form-group">
                        <label for="guest_name">Primary Contact / Guest Name *</label>
                        <input type="text" id="guest_name" name="guest_name" class="form-control" value="{{ old('guest_name', $user?->name) }}" required>
                    </div>

                    <div class="form-group">
                        <label for="guest_phone">Contact Phone Number *</label>
                        <input type="text" id="guest_phone" name="guest_phone" class="form-control" value="{{ old('guest_phone', $user?->phone) }}" placeholder="+855 12 888 999" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="guest_email">Email Address</label>
                    <input type="email" id="guest_email" name="guest_email" class="form-control" value="{{ old('guest_email', $user?->email) }}">
                </div>

                <div class="form-group">
                    <label for="special_requests">Special Requests / Occasion (Birthday, Anniversary, Quiet area)</label>
                    <textarea id="special_requests" name="special_requests" class="form-control" rows="2" placeholder="Tell us if you need baby chairs, candle setup, or specific dietary seating...">{{ old('special_requests') }}</textarea>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px; font-size: 15px; font-weight: bold;">
                    <i class="bi bi-calendar-plus"></i> Confirm Table Reservation
                </button>
            </div>
        @endif
    </form>
</div>
@endsection
