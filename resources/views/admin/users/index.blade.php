@extends('layouts.admin')

@section('title', 'Staff & User Governance — Admin')
@section('page-title', 'Staff & User Accounts Governance')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 10px;">
    <!-- Filter & Search Form -->
    <form method="GET" action="{{ route('admin.users.index') }}" style="display: flex; gap: 6px; flex-wrap: wrap;">
        <input type="text" name="search" class="form-control" placeholder="Search name, email, phone..." value="{{ request('search') }}" style="width: 220px;">
        <select name="role" class="form-select" style="width: 140px;">
            <option value="">All Roles</option>
            <option value="user" {{ request('role') == 'user' ? 'selected' : '' }}>Customer</option>
            <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Staff / Chef</option>
            <option value="super_admin" {{ request('role') == 'super_admin' ? 'selected' : '' }}>Super Admin</option>
        </select>
        <select name="status" class="form-select" style="width: 140px;">
            <option value="">All Statuses</option>
            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
        </select>
        <button type="submit" class="btn btn-secondary">Filter</button>
        @if(request()->hasAny(['search', 'role', 'status']))
            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Reset</a>
        @endif
    </form>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="simple-table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Role Assignment</th>
                    <th>Account Status</th>
                    <th>Registered Date</th>
                    <th style="text-align: right; width: 140px;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr>
                        <td>
                            <strong>{{ $user->name }}</strong>
                            <div style="font-size: 11px; color: var(--text-muted);">{{ $user->email }} • {{ $user->phone ?: 'No phone' }}</div>
                        </td>
                        <td>
                            <form method="POST" action="{{ route('admin.users.update-role', $user->id) }}" style="display: flex; gap: 4px; align-items: center;">
                                @csrf
                                @method('PUT')
                                <select name="role" class="form-select" style="padding: 2px 6px; font-size: 12px; width: 130px;" onchange="this.form.submit()" {{ $user->id === auth()->id() ? 'disabled' : '' }}>
                                    <option value="user" {{ $user->role == 'user' ? 'selected' : '' }}>Customer</option>
                                    <option value="admin" {{ $user->role == 'admin' ? 'selected' : '' }}>Staff Chef</option>
                                    <option value="super_admin" {{ $user->role == 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                                </select>
                            </form>
                        </td>
                        <td>
                            @if($user->status === 'active')
                                <span class="badge badge-active">Active</span>
                            @else
                                <span class="badge badge-inactive">Deactivated</span>
                            @endif
                        </td>
                        <td style="color: var(--text-muted); font-size: 12px;">
                            {{ $user->created_at->format('M d, Y') }}
                        </td>
                        <td style="text-align: right;">
                            @if($user->id !== auth()->id())
                                <form method="POST" action="{{ route('admin.users.toggle-status', $user->id) }}" style="display: inline;">
                                    @csrf
                                    @method('PUT')
                                    @if($user->status === 'active')
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Deactivate account for {{ $user->name }}?')">Deactivate</button>
                                    @else
                                        <button type="submit" class="btn btn-success btn-sm">Reactivate</button>
                                    @endif
                                </form>
                            @else
                                <span style="font-size: 11px; color: var(--text-muted);">(You)</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colSpan="5" style="text-align: center; padding: 20px; color: var(--text-muted);">No users found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination-wrapper">
        {{ $users->links() }}
    </div>
</div>
@endsection
