@extends('layouts.admin')

@section('title', 'Manage Table Reservations — Admin')
@section('page-title', 'Restaurant Table Bookings')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 10px;">
    <!-- Filter by Status & Date -->
    <form method="GET" action="{{ route('admin.reservations.index') }}" style="display: flex; gap: 6px; flex-wrap: wrap;">
        <input type="date" name="date" class="form-control" value="{{ request('date') }}" style="width: 150px;">
        <select name="status" class="form-select" style="width: 140px;">
            <option value="">All Statuses</option>
            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
            <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
        </select>
        <input type="text" name="search" class="form-control" placeholder="Search customer, phone..." value="{{ request('search') }}" style="width: 180px;">
        <button type="submit" class="btn btn-secondary"><i class="bi bi-funnel"></i> Filter</button>
        @if(request()->hasAny(['date', 'status', 'search']))
            <a href="{{ route('admin.reservations.index') }}" class="btn btn-secondary"><i class="bi bi-x-circle"></i> Reset</a>
        @endif
    </form>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="simple-table">
            <thead>
                <tr>
                    <th>Booking #</th>
                    <th>Table</th>
                    <th>Date & Time</th>
                    <th>Customer Details</th>
                    <th>Party Size</th>
                    <th>Status</th>
                    <th style="text-align: right; width: 140px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reservations as $res)
                    <tr>
                        <td>
                            <strong><a href="{{ route('admin.reservations.show', $res->id) }}"><i class="bi bi-calendar-check"></i> {{ $res->reservation_number }}</a></strong>
                        </td>
                        <td>
                            @if($res->table)
                                <strong><i class="bi bi-grid"></i> {{ $res->table->table_number }}</strong> ({{ $res->table->location }})
                            @else
                                <span style="color: var(--text-muted);">{{ $res->table_type ?: 'Standard' }}</span>
                            @endif
                        </td>
                        <td>
                            <strong>{{ date('M d, Y', strtotime($res->reservation_date)) }}</strong> at {{ $res->reservation_time }}
                        </td>
                        <td>
                            <div><strong>{{ $res->guest_name }}</strong></div>
                            <div style="font-size: 11px; color: var(--text-muted);"><i class="bi bi-telephone"></i> {{ $res->guest_phone }}</div>
                        </td>
                        <td>
                            <i class="bi bi-people"></i> <strong>{{ $res->guest_count }}</strong>
                        </td>
                        <td>
                            @php
                                $badgeClass = match($res->status) {
                                    'pending' => 'badge-pending',
                                    'confirmed' => 'badge-confirmed',
                                    'completed' => 'badge-completed',
                                    'rejected', 'cancelled' => 'badge-cancelled',
                                    default => 'badge-pending'
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }}">{{ ucfirst($res->status) }}</span>
                        </td>
                        <td style="text-align: right;">
                            <a href="{{ route('admin.reservations.show', $res->id) }}" class="btn btn-secondary btn-sm"><i class="bi bi-sliders"></i> Process</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colSpan="7" style="text-align: center; padding: 20px; color: var(--text-muted);">No table reservations found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 18px; flex-wrap: wrap; gap: 10px; font-size: 13px; color: var(--text-muted);">
        <div>
            Showing <strong>{{ $reservations->firstItem() ?? 0 }}</strong> to <strong>{{ $reservations->lastItem() ?? 0 }}</strong> of <strong>{{ $reservations->total() }}</strong> bookings
        </div>
        <div>
            {{ $reservations->links() }}
        </div>
    </div>
</div>
@endsection
