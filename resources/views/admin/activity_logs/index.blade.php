@extends('layouts.admin')

@section('title', 'System Activity Logs')
@section('page-title', 'Management Audit & Activity Trail')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 10px;">
    <form method="GET" action="{{ route('admin.activity-logs.index') }}" style="display: flex; gap: 6px;">
        <input type="text" name="search" class="form-control" placeholder="Search description..." value="{{ request('search') }}" style="width: 220px;">
        <input type="text" name="action" class="form-control" placeholder="Filter action..." value="{{ request('action') }}" style="width: 160px;">
        <button type="submit" class="btn btn-secondary"><i class="bi bi-search"></i></button>
        @if(request()->hasAny(['search', 'action']))
            <a href="{{ route('admin.activity-logs.index') }}" class="btn btn-secondary"><i class="bi bi-x-circle"></i> Reset</a>
        @endif
    </form>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="simple-table">
            <thead>
                <tr>
                    <th>Timestamp</th>
                    <th>User / Staff</th>
                    <th>Action</th>
                    <th>Description</th>
                    <th>IP Address</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td style="font-size: 12px; color: var(--text-muted); white-space: nowrap;">
                            {{ $log->created_at->format('M d, Y H:i:s') }}
                        </td>
                        <td>
                            <strong>{{ $log->user ? $log->user->name : 'System' }}</strong>
                            @if($log->user)
                                <div style="font-size: 11px; color: var(--text-muted);">{{ $log->user->role }}</div>
                            @endif
                        </td>
                        <td>
                            <code style="font-size: 12px; color: var(--primary);">{{ $log->action }}</code>
                        </td>
                        <td style="font-size: 13px;">
                            {{ $log->description }}
                        </td>
                        <td style="font-size: 12px; color: var(--text-muted);">
                            {{ $log->ip_address ?: '—' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 20px; color: var(--text-muted);">No activity logs recorded yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top: 16px;">
        {{ $logs->links() }}
    </div>
</div>
@endsection
