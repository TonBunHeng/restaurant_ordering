@extends('layouts.admin')

@section('title', 'Process Booking #' . $reservation->reservation_number . ' — Admin')
@section('page-title', 'Reservation Details & Action')

@section('content')
<div style="margin-bottom: 14px;">
    <a href="{{ route('admin.reservations.index') }}" style="font-size: 13px;">&larr; Back to Reservations List</a>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 24px;">
    <!-- Left: Booking Info -->
    <div>
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Booking #{{ $reservation->reservation_number }}</h3>
                <span class="badge badge-{{ $reservation->status == 'confirmed' ? 'confirmed' : ($reservation->status == 'pending' ? 'pending' : 'cancelled') }}">
                    {{ ucfirst($reservation->status) }}
                </span>
            </div>

            <div style="display: grid; gap: 12px; font-size: 13px;">
                <div>
                    <span style="color: var(--text-muted);">Assigned Table:</span>
                    <strong>
                        @if($reservation->table)
                            {{ $reservation->table->table_number }} ({{ $reservation->table->location }}, Max {{ $reservation->table->capacity }} seats)
                        @else
                            {{ $reservation->table_type ?: 'Standard' }}
                        @endif
                    </strong>
                </div>

                <div>
                    <span style="color: var(--text-muted);">Reservation Date & Time:</span>
                    <strong>{{ date('l, F d, Y', strtotime($reservation->reservation_date)) }} at {{ $reservation->reservation_time }}</strong>
                </div>

                <div>
                    <span style="color: var(--text-muted);">Guest Count:</span>
                    <strong>{{ $reservation->guest_count }} People</strong>
                </div>

                <hr style="border: none; border-top: 1px solid var(--border); margin: 4px 0;">

                <div>
                    <span style="color: var(--text-muted);">Customer Name:</span>
                    <strong>{{ $reservation->guest_name }}</strong>
                </div>

                <div>
                    <span style="color: var(--text-muted);">Contact Phone:</span>
                    <strong>{{ $reservation->guest_phone }}</strong>
                </div>

                @if($reservation->guest_email)
                    <div>
                        <span style="color: var(--text-muted);">Email Address:</span>
                        <span>{{ $reservation->guest_email }}</span>
                    </div>
                @endif

                @if($reservation->special_requests)
                    <div style="background: #f8fafc; padding: 10px; border-radius: var(--radius); border: 1px solid var(--border); margin-top: 6px;">
                        <div style="font-weight: bold; font-size: 11px; color: #475569; margin-bottom: 2px;">Special Requests:</div>
                        <div style="font-size: 13px; color: #b45309;">{{ $reservation->special_requests }}</div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Right: Update Status & Reassign Table -->
    <div>
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Staff Actions</h3>
            </div>

            <form method="POST" action="{{ route('admin.reservations.update-status', $reservation->id) }}">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label class="form-label" for="status">Update Booking Status *</label>
                    <select id="status" name="status" class="form-select" required>
                        <option value="pending" {{ $reservation->status == 'pending' ? 'selected' : '' }}>Pending (Awaiting Confirmation)</option>
                        <option value="confirmed" {{ $reservation->status == 'confirmed' ? 'selected' : '' }}>Confirmed (Accept Booking)</option>
                        <option value="completed" {{ $reservation->status == 'completed' ? 'selected' : '' }}>Completed (Diners Finished)</option>
                        <option value="rejected" {{ $reservation->status == 'rejected' ? 'selected' : '' }}>Rejected (Decline Booking)</option>
                        <option value="cancelled" {{ $reservation->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="table_id">Assigned Dining Table</label>
                    <select id="table_id" name="table_id" class="form-select">
                        <option value="">-- Keep Current Table --</option>
                        @foreach($tables as $tbl)
                            <option value="{{ $tbl->id }}" {{ $reservation->table_id == $tbl->id ? 'selected' : '' }}>
                                {{ $tbl->table_number }} ({{ $tbl->location }}, {{ $tbl->capacity }} seats)
                            </option>
                        @endforeach
                    </select>
                </div>

                <div style="margin-top: 20px;">
                    <button type="submit" class="btn btn-primary" style="width: 100%;">Save Action</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
