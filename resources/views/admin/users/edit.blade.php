@extends('layouts.admin')

@section('title', 'Edit Staff Account')
@section('page-title', 'Edit Account: ' . $user->name)

@section('content')
<div style="max-width: 600px;">
    <div class="card">
        <form method="POST" action="{{ route('admin.users.update', $user->id) }}">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="name">Full Name *</label>
                <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
            </div>

            <div class="form-group">
                <label for="email">Email Address *</label>
                <input type="email" id="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
            </div>

            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="text" id="phone" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}">
            </div>

            <div class="form-group">
                <label for="password">New Password (Leave blank to keep unchanged)</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="Optional new password">
            </div>

            <div class="grid grid-2" style="gap: 12px;">
                <div class="form-group">
                    <label for="role">User Role *</label>
                    <select id="role" name="role" class="form-select" required>
                        <option value="staff" {{ old('role', $user->role) === 'staff' ? 'selected' : '' }}>Staff</option>
                        <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="super_admin" {{ old('role', $user->role) === 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                        <option value="user" {{ old('role', $user->role) === 'user' ? 'selected' : '' }}>Customer</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="status">Account Status *</label>
                    <select id="status" name="status" class="form-select" required>
                        <option value="active" {{ old('status', $user->status) === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="suspended" {{ old('status', $user->status) === 'suspended' ? 'selected' : '' }}>Suspended</option>
                    </select>
                </div>
            </div>

            <div style="display: flex; gap: 8px; margin-top: 20px;">
                <button type="submit" class="btn btn-primary">Update Account</button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
