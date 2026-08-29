@extends('layouts.admin')

@section('title', 'Manage Customers')
@section('page-title', 'Customer Directory & Accounts')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 10px;">
    <form method="GET" action="{{ route('admin.customers.index') }}" style="display: flex; gap: 6px;">
        <input type="text" name="search" class="form-control" placeholder="Search name, email, phone..." value="{{ request('search') }}" style="width: 240px;">
        <select name="status" class="form-select" style="width: 140px;">
            <option value="">All Statuses</option>
            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
            <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
        </select>
        <button type="submit" class="btn btn-secondary"><i class="bi bi-search"></i></button>
        @if(request()->hasAny(['search', 'status']))
            <a href="{{ route('admin.customers.index') }}" class="btn btn-secondary"><i class="bi bi-x-circle"></i> Reset</a>
        @endif
    </form>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="simple-table">
            <thead>
                <tr>
                    <th>Customer Name</th>
                    <th>Contact</th>
                    <th>Total Orders</th>
                    <th>Table Bookings</th>
                    <th>Status</th>
                    <th>Registered</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $customer)
                    <tr>
                        <td>
                            <strong style="font-size: 14px;">{{ $customer->name }}</strong>
                        </td>
                        <td>
                            <div>{{ $customer->email }}</div>
                            <small style="color: var(--text-muted);">{{ $customer->phone ?: 'No phone provided' }}</small>
                        </td>
                        <td>
                            <strong>{{ $customer->orders_count }}</strong> orders
                        </td>
                        <td>
                            <strong>{{ $customer->reservations_count }}</strong> bookings
                        </td>
                        <td>
                            <span class="badge {{ $customer->status === 'active' ? 'badge-success' : 'badge-danger' }}">
                                {{ ucfirst($customer->status) }}
                            </span>
                        </td>
                        <td style="font-size: 12px; color: var(--text-muted);">
                            {{ $customer->created_at->format('M d, Y') }}
                        </td>
                        <td style="text-align: right;">
                            <div style="display: inline-flex; gap: 4px;">
                                <a href="{{ route('admin.customers.show', $customer->id) }}" class="btn btn-secondary btn-sm"><i class="bi bi-eye"></i> Details</a>
                                <form method="POST" action="{{ route('admin.customers.toggle-status', $customer->id) }}" onsubmit="return confirm('Change status for {{ $customer->name }}?');">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="btn btn-sm {{ $customer->status === 'active' ? 'btn-danger' : 'btn-primary' }}">
                                        {{ $customer->status === 'active' ? 'Suspend' : 'Activate' }}
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 20px; color: var(--text-muted);">No customer accounts found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top: 16px;">
        {{ $customers->links() }}
    </div>
</div>
@endsection
