@extends('layouts.admin')

@section('title', 'Create Staff Account')
@section('page-title', 'Create New Staff or Admin Account')

@section('content')
<div style="max-width: 600px;">
    <div class="card">
        <form method="POST" action="{{ route('admin.users.store') }}">
            @csrf

            <div class="form-group">
                <label for="name">Full Name *</label>
                <input type="text" id="name" name="name" class="form-control" value="{{ old('name') }}" required>
            </div>

            <div class="form-group">
                <label for="email">Email Address *</label>
                <input type="email" id="email" name="email" class="form-control" value="{{ old('email') }}" required>
            </div>

            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="text" id="phone" name="phone" class="form-control" value="{{ old('phone') }}">
            </div>

            <div class="form-group">
                <label for="password">Password * (Min 6 chars)</label>
                <input type="password" id="password" name="password" class="form-control" required minlength="6">
            </div>

            <div class="grid grid-2" style="gap: 12px;">
                <div class="form-group">
                    <label for="role">User Role *</label>
                    <select id="role" name="role" class="form-select" required>
                        <option value="staff">Staff (Kitchen / Orders / Bookings)</option>
                        <option value="admin">Admin (Manager)</option>
                        <option value="super_admin">Super Admin (Full Access)</option>
                        <option value="user">Customer</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="status">Account Status *</label>
                    <select id="status" name="status" class="form-select" required>
                        <option value="active">Active</option>
                        <option value="suspended">Suspended</option>
                    </select>
                </div>
            </div>

            <div style="display: flex; gap: 8px; margin-top: 20px;">
                <button type="submit" class="btn btn-primary">Create Account</button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
