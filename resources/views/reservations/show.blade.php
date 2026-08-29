@extends('layouts.app')

@section('title', 'Reservation Confirmation #' . $reservation->reservation_number)

@section('content')
<div style="max-width: 700px; margin: 0 auto;">
    <div style="margin-bottom: 16px; display: flex; justify-content: space-between; align-items: center;">
        <a href="{{ route('reservations.index') }}" style="color: var(--text-muted); font-size: 13px;">
            <i class="bi bi-arrow-left"></i> Back to My Bookings
        </a>
        <button onclick="window.print()" class="btn btn-secondary btn-sm"><i class="bi bi-printer"></i> Print</button>
    </div>

    <div class="card">
        <div style="text-align: center; border-bottom: 1px solid var(--border); padding-bottom: 20px; margin-bottom: 20px;">
            <div style="width: 50px; height: 50px; border-radius: 50%; background: #dcfce7; color: #15803d; display: flex; align-items: center; justify-content: center; font-size: 24px; margin: 0 auto 12px;">
                <i class="bi bi-calendar-check-fill"></i>
            </div>
            <h1 style="font-size: 22px; font-weight: 800; margin-bottom: 4px;">Table Reservation</h1>
            <p style="font-size: 14px; color: var(--text-muted);">Booking Reference: <strong>#{{ $reservation->reservation_number }}</strong></p>
            <div style="margin-top: 8px;">
                @php
                    $badgeClass = match($reservation->status) {
                        'confirmed' => 'badge-success',
                        'cancelled', 'rejected' => 'badge-danger',
                        'completed' => 'badge-info',
                        default => 'badge-warning'
                    };
                @endphp
                <span class="badge {{ $badgeClass }}" style="font-size: 13px; padding: 4px 10px;">
                    {{ strtoupper($reservation->status) }}
                </span>
            </div>
        </div>

        <div style="display: flex; flex-direction: column; gap: 14px; font-size: 14px; margin-bottom: 24px;">
            <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border); padding-bottom: 8px;">
                <span style="color: var(--text-muted);">Table Number:</span>
                <strong>{{ $reservation->table ? $reservation->table->table_number : 'Standard Table' }}</strong>
            </div>

            <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border); padding-bottom: 8px;">
                <span style="color: var(--text-muted);">Seating Location:</span>
                <span>{{ $reservation->table_type ?: 'Main Dining' }}</span>
            </div>

            <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border); padding-bottom: 8px;">
                <span style="color: var(--text-muted);">Reservation Date:</span>
                <strong>{{ $reservation->reservation_date->format('l, F d, Y') }}</strong>
            </div>

            <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border); padding-bottom: 8px;">
                <span style="color: var(--text-muted);">Reservation Time:</span>
                <strong>{{ $reservation->reservation_time }}</strong>
            </div>

            <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border); padding-bottom: 8px;">
                <span style="color: var(--text-muted);">Number of Guests:</span>
                <span>{{ $reservation->guest_count }} Guests</span>
            </div>

            <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border); padding-bottom: 8px;">
                <span style="color: var(--text-muted);">Guest Name:</span>
                <span>{{ $reservation->guest_name }} ({{ $reservation->guest_phone }})</span>
            </div>

            @if($reservation->special_requests)
                <div style="background: var(--bg-page); padding: 10px 14px; border-radius: var(--radius); border: 1px solid var(--border);">
                    <strong style="font-size: 13px;">Special Requests:</strong>
                    <p style="font-size: 13px; color: var(--text-main); margin-top: 4px;">{{ $reservation->special_requests }}</p>
                </div>
            @endif
        </div>

        @if(in_array($reservation->status, ['pending', 'confirmed']))
            <div style="border-top: 1px solid var(--border); padding-top: 16px; display: flex; justify-content: space-between; align-items: center;">
                <small style="color: var(--text-muted);">Need to change or cancel? Cancellations are allowed up to 2 hours in advance.</small>
                <form method="POST" action="{{ route('reservations.cancel', $reservation->id) }}" onsubmit="return confirm('Are you sure you want to cancel this reservation?');">
                    @csrf
                    <button type="submit" class="btn btn-secondary btn-sm" style="color: var(--danger);"><i class="bi bi-x-circle"></i> Cancel Booking</button>
                </form>
            </div>
        @endif
    </div>
</div>
@endsection
