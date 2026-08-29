@extends('layouts.admin')

@section('title', 'Add Dining Table — Admin')
@section('page-title', 'Add New Dining Table')

@section('content')
<div style="max-width: 600px;">
    <div class="card">
        <form method="POST" action="{{ route('admin.tables.store') }}">
            @csrf

            <div class="grid grid-2">
                <div class="form-group">
                    <label class="form-label" for="table_number">Table Number / Label *</label>
                    <input type="text" id="table_number" name="table_number" class="form-control" value="{{ old('table_number') }}" required placeholder="e.g. Table 11">
                </div>

                <div class="form-group">
                    <label class="form-label" for="capacity">Seating Capacity (Guests) *</label>
                    <input type="number" id="capacity" name="capacity" class="form-control" value="{{ old('capacity', 4) }}" required min="1" max="50">
                </div>
            </div>

            <div class="grid grid-2">
                <div class="form-group">
                    <label class="form-label" for="location">Dining Area / Location *</label>
                    <select id="location" name="location" class="form-select" required>
                        @foreach($locations as $loc)
                            <option value="{{ $loc }}" {{ old('location') == $loc ? 'selected' : '' }}>{{ $loc }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="status">Table Status *</label>
                    <select id="status" name="status" class="form-select" required>
                        <option value="available" {{ old('status', 'available') == 'available' ? 'selected' : '' }}>Available</option>
                        <option value="reserved" {{ old('status') == 'reserved' ? 'selected' : '' }}>Reserved</option>
                        <option value="occupied" {{ old('status') == 'occupied' ? 'selected' : '' }}>Occupied</option>
                        <option value="unavailable" {{ old('status') == 'unavailable' ? 'selected' : '' }}>Unavailable / Maintenance</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="description">Table Notes / Description</label>
                <textarea id="description" name="description" class="form-control" rows="2" placeholder="e.g. Corner table with view of the courtyard fountain...">{{ old('description') }}</textarea>
            </div>

            <div style="margin-top: 20px; display: flex; gap: 10px;">
                <button type="submit" class="btn btn-primary">Save Table</button>
                <a href="{{ route('admin.tables.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
