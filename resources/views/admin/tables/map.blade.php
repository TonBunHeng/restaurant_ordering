@extends('layouts.admin')

@section('title', 'Restaurant Table Map Layout')
@section('page-title', 'Dining Tables Floor Plan & Live Status')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
    <div>
        <p style="color: var(--text-muted); font-size: 13px;">Visual layout of tables across restaurant dining zones.</p>
    </div>
    <div style="display: flex; gap: 8px;">
        <a href="{{ route('admin.tables.index') }}" class="btn btn-secondary btn-sm"><i class="bi bi-list"></i> Table List View</a>
        <a href="{{ route('admin.tables.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-circle"></i> Add Table</a>
    </div>
</div>

<!-- Legend Bar -->
<div class="card" style="margin-bottom: 20px; padding: 12px 16px; display: flex; gap: 20px; align-items: center; flex-wrap: wrap; font-size: 13px;">
    <strong>Status Legend:</strong>
    <span style="display: flex; align-items: center; gap: 6px;"><span style="width: 14px; height: 14px; background: #dcfce7; border: 1px solid #16a34a; border-radius: 3px;"></span> Available</span>
    <span style="display: flex; align-items: center; gap: 6px;"><span style="width: 14px; height: 14px; background: #fef3c7; border: 1px solid #d97706; border-radius: 3px;"></span> Reserved Today</span>
    <span style="display: flex; align-items: center; gap: 6px;"><span style="width: 14px; height: 14px; background: #fee2e2; border: 1px solid #dc2626; border-radius: 3px;"></span> Occupied / Dine-in</span>
    <span style="display: flex; align-items: center; gap: 6px;"><span style="width: 14px; height: 14px; background: #f1f5f9; border: 1px solid #94a3b8; border-radius: 3px;"></span> Unavailable</span>
</div>

<!-- Tables Grouped by Location Zone -->
@php
    $grouped = $tables->groupBy('location');
@endphp

@foreach($grouped as $location => $zoneTables)
    <div class="card" style="margin-bottom: 20px;">
        <h2 style="font-size: 16px; font-weight: bold; margin-bottom: 16px; border-bottom: 1px solid var(--border); padding-bottom: 8px; color: var(--primary);">
            <i class="bi bi-geo-alt-fill"></i> Zone: {{ $location }} ({{ $zoneTables->count() }} Tables)
        </h2>

        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 16px;">
            @foreach($zoneTables as $table)
                @php
                    $isOccupied = isset($activeDineInOrders[$table->table_number]) || $table->status === 'occupied';
                    $hasReservationToday = $table->reservations->isNotEmpty() || $table->status === 'reserved';

                    $bg = '#dcfce7'; // green
                    $border = '#16a34a';
                    $statusText = 'Available';

                    if ($table->status === 'unavailable') {
                        $bg = '#f1f5f9';
                        $border = '#94a3b8';
                        $statusText = 'Unavailable';
                    } elseif ($isOccupied) {
                        $bg = '#fee2e2';
                        $border = '#dc2626';
                        $statusText = 'Occupied';
                    } elseif ($hasReservationToday) {
                        $bg = '#fef3c7';
                        $border = '#d97706';
                        $statusText = 'Reserved Today';
                    }
                @endphp

                <div style="background: {{ $bg }}; border: 2px solid {{ $border }}; border-radius: var(--radius); padding: 14px; text-align: center; display: flex; flex-direction: column; justify-content: space-between; min-height: 140px;">
                    <div>
                        <div style="font-size: 18px; font-weight: 800; color: #1e293b;">{{ $table->table_number }}</div>
                        <div style="font-size: 12px; color: #475569; margin: 4px 0;">
                            <i class="bi bi-people-fill"></i> <strong>{{ $table->capacity }} Seats</strong>
                        </div>
                        <div style="font-size: 11px; font-weight: bold; text-transform: uppercase; color: {{ $border }};">
                            {{ $statusText }}
                        </div>

                        @if(isset($activeDineInOrders[$table->table_number]))
                            <div style="font-size: 10px; background: #ffffff; padding: 2px 4px; border-radius: 3px; margin-top: 4px; border: 1px solid {{ $border }};">
                                Active Order: #{{ $activeDineInOrders[$table->table_number]->order_number }}
                            </div>
                        @elseif($table->reservations->isNotEmpty())
                            <div style="font-size: 10px; background: #ffffff; padding: 2px 4px; border-radius: 3px; margin-top: 4px; border: 1px solid {{ $border }};">
                                Booked: {{ $table->reservations->first()->reservation_time }} ({{ $table->reservations->first()->guest_name }})
                            </div>
                        @endif
                    </div>

                    <div style="margin-top: 10px; display: flex; justify-content: center; gap: 6px;">
                        <a href="{{ route('admin.tables.edit', $table->id) }}" class="btn btn-secondary btn-sm" style="font-size: 11px; padding: 2px 6px;">Edit</a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endforeach
@endsection
