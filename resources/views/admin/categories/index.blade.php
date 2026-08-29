@extends('layouts.admin')

@section('title', 'Manage Categories — Admin')
@section('page-title', 'Menu Categories Management')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
    <div>Manage food classification, menu display order, and active statuses.</div>
    <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">+ Add New Category</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="simple-table">
            <thead>
                <tr>
                    <th style="width: 50px;">Order</th>
                    <th>Category Name</th>
                    <th>Slug</th>
                    <th>Dishes Count</th>
                    <th>Status</th>
                    <th style="text-align: right; width: 140px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $cat)
                    <tr>
                        <td style="text-align: center; font-weight: bold;">{{ $cat->order }}</td>
                        <td>
                            <strong>{{ $cat->name }}</strong>
                            @if($cat->description)
                                <div style="font-size: 11px; color: var(--text-muted);">{{ Str::limit($cat->description, 60) }}</div>
                            @endif
                        </td>
                        <td style="font-family: monospace; color: var(--text-muted);">{{ $cat->slug }}</td>
                        <td>
                            <strong>{{ $cat->dishes_count }}</strong> dishes
                        </td>
                        <td>
                            @if($cat->is_active)
                                <span class="badge badge-active">Active</span>
                            @else
                                <span class="badge badge-inactive">Disabled</span>
                            @endif
                        </td>
                        <td style="text-align: right;">
                            <div style="display: flex; gap: 4px; justify-content: flex-end;">
                                <a href="{{ route('admin.categories.edit', $cat->id) }}" class="btn btn-secondary btn-sm">Edit</a>
                                <form method="POST" action="{{ route('admin.categories.destroy', $cat->id) }}" onsubmit="return confirm('Delete category {{ $cat->name }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colSpan="6" style="text-align: center; padding: 20px; color: var(--text-muted);">No categories found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
