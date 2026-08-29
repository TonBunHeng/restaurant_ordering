@extends('layouts.admin')

@section('title', 'Edit Table — Admin')
@section('page-title', 'Edit Table: ' . $table->table_number)

@section('content')
<div style="max-width: 600px;">
    <div class="card">
        <form method="POST" action="{{ route('admin.tables.update', $table->id) }}">
            @csrf
            @method('PUT')

            <div class="grid grid-2">
                <div class="form-group">
                    <label class="form-label" for="table_number">Table Number / Label *</label>
                    <input type="text" id="table_number" name="table_number" class="form-control" value="{{ old('table_number', $table->table_number) }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="capacity">Seating Capacity (Guests) *</label>
                    <input type="number" id="capacity" name="capacity" class="form-control" value="{{ old('capacity', $table->capacity) }}" required min="1" max="50">
                </div>
            </div>

            <div class="grid grid-2">
                <div class="form-group">
                    <label class="form-label" for="location">Dining Area / Location *</label>
                    <select id="location" name="location" class="form-select" required>
                        @foreach($locations as $loc)
                            <option value="{{ $loc }}" {{ old('location', $table->location) == $loc ? 'selected' : '' }}>{{ $loc }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="status">Table Status *</label>
                    <select id="status" name="status" class="form-select" required>
                        <option value="available" {{ old('status', $table->status) == 'available' ? 'selected' : '' }}>Available</option>
                        <option value="reserved" {{ old('status', $table->status) == 'reserved' ? 'selected' : '' }}>Reserved</option>
                        <option value="occupied" {{ old('status', $table->status) == 'occupied' ? 'selected' : '' }}>Occupied</option>
                        <option value="unavailable" {{ old('status', $table->status) == 'unavailable' ? 'selected' : '' }}>Unavailable / Maintenance</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="description">Table Notes / Description</label>
                <textarea id="description" name="description" class="form-control" rows="2">{{ old('description', $table->description) }}</textarea>
            </div>

            <div style="margin-top: 20px; display: flex; gap: 10px;">
                <button type="submit" class="btn btn-primary">Update Table</button>
                <a href="{{ route('admin.tables.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
