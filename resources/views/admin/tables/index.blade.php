@extends('layouts.admin')

@section('title', 'Manage Dining Tables — Admin')
@section('page-title', 'Restaurant Dining Tables Management')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 10px;">
    <!-- Filter by Location and Status -->
    <form method="GET" action="{{ route('admin.tables.index') }}" style="display: flex; gap: 6px; flex-wrap: wrap;">
        <select name="location" class="form-select" style="width: 160px;">
            <option value="">All Locations</option>
            @foreach($locations as $loc)
                <option value="{{ $loc }}" {{ request('location') == $loc ? 'selected' : '' }}>{{ $loc }}</option>
            @endforeach
        </select>
        <select name="status" class="form-select" style="width: 140px;">
            <option value="">All Statuses</option>
            <option value="available" {{ request('status') == 'available' ? 'selected' : '' }}>Available</option>
            <option value="reserved" {{ request('status') == 'reserved' ? 'selected' : '' }}>Reserved</option>
            <option value="occupied" {{ request('status') == 'occupied' ? 'selected' : '' }}>Occupied</option>
            <option value="unavailable" {{ request('status') == 'unavailable' ? 'selected' : '' }}>Unavailable</option>
        </select>
        <button type="submit" class="btn btn-secondary"><i class="bi bi-funnel"></i> Filter</button>
        @if(request()->hasAny(['location', 'status']))
            <a href="{{ route('admin.tables.index') }}" class="btn btn-secondary"><i class="bi bi-x-circle"></i> Reset</a>
        @endif
    </form>

    <div style="display: flex; gap: 8px;">
        <a href="{{ route('admin.tables.map') }}" class="btn btn-secondary"><i class="bi bi-grid-3x3-gap"></i> Table Floor Map</a>
        <a href="{{ route('admin.tables.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Add New Table</a>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="simple-table">
            <thead>
                <tr>
                    <th>Table Number</th>
                    <th>Seating Capacity</th>
                    <th>Dining Location</th>
                    <th>Status</th>
                    <th>Upcoming Bookings</th>
                    <th>Description</th>
                    <th style="text-align: right; width: 140px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tables as $table)
                    <tr>
                        <td>
                            <strong style="font-size: 14px; color: var(--primary);"><i class="bi bi-grid"></i> {{ $table->table_number }}</strong>
                        </td>
                        <td>
                            <strong>{{ $table->capacity }}</strong> seats
                        </td>
                        <td>
                            <span style="font-weight: 600;">{{ $table->location }}</span>
                        </td>
                        <td>
                            @php
                                $badgeClass = match($table->status) {
                                    'available' => 'badge-available',
                                    'reserved' => 'badge-pending',
                                    'occupied' => 'badge-occupied',
                                    'unavailable' => 'badge-unavailable',
                                    default => 'badge-available'
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }}">{{ ucfirst($table->status) }}</span>
                        </td>
                        <td>
                            @if($table->reservations_count > 0)
                                <span class="badge badge-pending">{{ $table->reservations_count }} active</span>
                            @else
                                <span style="color: var(--text-muted); font-size: 12px;">None</span>
                            @endif
                        </td>
                        <td style="font-size: 12px; color: var(--text-muted);">
                            {{ $table->description ?: '—' }}
                        </td>
                        <td style="text-align: right;">
                            <div style="display: flex; gap: 4px; justify-content: flex-end;">
                                <a href="{{ route('admin.tables.edit', $table->id) }}" class="btn btn-secondary btn-sm"><i class="bi bi-pencil"></i> Edit</a>
                                <form method="POST" action="{{ route('admin.tables.destroy', $table->id) }}" onsubmit="return confirm('Delete {{ $table->table_number }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i> Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 20px; color: var(--text-muted);">No tables found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top: 16px;">
        {{ $tables->links() }}
    </div>
</div>
@endsection
