@extends('layouts.admin')

@section('title', 'Manage Reviews')
@section('page-title', 'Customer Reviews & Feedback')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 10px;">
    <form method="GET" action="{{ route('admin.reviews.index') }}" style="display: flex; gap: 6px;">
        <select name="status" class="form-select" style="width: 140px;">
            <option value="">All Statuses</option>
            <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Published</option>
            <option value="hidden" {{ request('status') === 'hidden' ? 'selected' : '' }}>Hidden</option>
            <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
        </select>
        <select name="rating" class="form-select" style="width: 120px;">
            <option value="">All Stars</option>
            <option value="5" {{ request('rating') == '5' ? 'selected' : '' }}>5 Stars</option>
            <option value="4" {{ request('rating') == '4' ? 'selected' : '' }}>4 Stars</option>
            <option value="3" {{ request('rating') == '3' ? 'selected' : '' }}>3 Stars</option>
            <option value="2" {{ request('rating') == '2' ? 'selected' : '' }}>2 Stars</option>
            <option value="1" {{ request('rating') == '1' ? 'selected' : '' }}>1 Star</option>
        </select>
        <button type="submit" class="btn btn-secondary"><i class="bi bi-funnel"></i> Filter</button>
        @if(request()->hasAny(['status', 'rating']))
            <a href="{{ route('admin.reviews.index') }}" class="btn btn-secondary"><i class="bi bi-x-circle"></i> Reset</a>
        @endif
    </form>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="simple-table">
            <thead>
                <tr>
                    <th>Dish</th>
                    <th>Customer</th>
                    <th>Rating</th>
                    <th>Customer Comment</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th style="text-align: right;">Moderation</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reviews as $rev)
                    <tr>
                        <td>
                            @if($rev->dish)
                                <strong><a href="{{ route('menu.show', $rev->dish->slug) }}" target="_blank">{{ $rev->dish->name }}</a></strong>
                            @else
                                <span style="color: var(--text-muted);">Deleted Food</span>
                            @endif
                        </td>
                        <td>{{ $rev->user ? $rev->user->name : 'Anonymous' }}</td>
                        <td>
                            <span style="color: #b45309; font-weight: bold;">
                                @for($i=1; $i<=$rev->rating; $i++) ★ @endfor
                            </span>
                        </td>
                        <td style="font-size: 13px; max-width: 250px;">
                            "{{ $rev->comment }}"
                        </td>
                        <td>
                            <span class="badge {{ $rev->status === 'published' ? 'badge-success' : 'badge-danger' }}">
                                {{ ucfirst($rev->status) }}
                            </span>
                        </td>
                        <td style="font-size: 12px; color: var(--text-muted);">{{ $rev->created_at->format('M d, Y') }}</td>
                        <td style="text-align: right;">
                            <div style="display: inline-flex; gap: 4px;">
                                @if($rev->status === 'published')
                                    <form method="POST" action="{{ route('admin.reviews.update-status', $rev->id) }}">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="status" value="hidden">
                                        <button type="submit" class="btn btn-secondary btn-sm" title="Hide Review">Hide</button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('admin.reviews.update-status', $rev->id) }}">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="status" value="published">
                                        <button type="submit" class="btn btn-primary btn-sm" title="Publish Review">Publish</button>
                                    </form>
                                @endif

                                <form method="POST" action="{{ route('admin.reviews.destroy', $rev->id) }}" onsubmit="return confirm('Permanently delete this review?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 20px; color: var(--text-muted);">No reviews found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top: 16px;">
        {{ $reviews->links() }}
    </div>
</div>
@endsection
