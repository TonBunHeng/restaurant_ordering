@extends('layouts.app')

@section('title', 'My Table Bookings - ' . \App\Models\RestaurantSetting::get('name', 'Restaurant'))

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <div>
        <h1 style="font-size: 22px; font-weight: 800;">My Table Bookings</h1>
        <p style="font-size: 13px; color: var(--text-muted);">Manage all your current and past dining reservations.</p>
    </div>
    <a href="{{ route('reservations.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-calendar-plus"></i> Book a Table</a>
</div>

@if($reservations->isEmpty())
    <div class="card" style="text-align: center; padding: 60px 20px;">
        <i class="bi bi-calendar-x" style="font-size: 48px; color: var(--text-muted); display: block; margin-bottom: 12px;"></i>
        <h2 style="font-size: 18px; font-weight: bold; margin-bottom: 8px;">No table bookings found</h2>
        <p style="color: var(--text-muted); font-size: 14px; margin-bottom: 20px;">You haven't reserved any dining tables yet.</p>
        <a href="{{ route('reservations.create') }}" class="btn btn-primary"><i class="bi bi-calendar-date"></i> Book a Table Now</a>
    </div>
@else
    <div class="card">
        <table class="table">
            <thead>
                <tr>
                    <th>Booking #</th>
                    <th>Date & Time</th>
                    <th>Table</th>
                    <th>Location</th>
                    <th>Guests</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($reservations as $res)
                    <tr>
                        <td><strong>{{ $res->reservation_number }}</strong></td>
                        <td>{{ $res->reservation_date->format('M d, Y') }} at {{ $res->reservation_time }}</td>
                        <td><strong>{{ $res->table ? $res->table->table_number : 'Standard Table' }}</strong></td>
                        <td>{{ $res->table_type ?: 'Main Dining' }}</td>
                        <td>{{ $res->guest_count }} Guests</td>
                        <td>
                            @php
                                $badgeClass = match($res->status) {
                                    'confirmed' => 'badge-success',
                                    'cancelled', 'rejected' => 'badge-danger',
                                    'completed' => 'badge-info',
                                    default => 'badge-warning'
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }}">{{ ucfirst($res->status) }}</span>
                        </td>
                        <td>
                            <a href="{{ route('reservations.show', $res->id) }}" class="btn btn-secondary btn-sm">Details</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div style="margin-top: 16px;">
            {{ $reservations->links() }}
        </div>
    </div>
@endif
@endsection
