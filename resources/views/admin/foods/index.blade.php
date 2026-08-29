@extends('layouts.admin')

@section('title', 'Manage Food & Menu — Admin')
@section('page-title', 'Menu Dishes & Food Items')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 10px;">
    <!-- Search & Filter Form -->
    <form method="GET" action="{{ route('admin.foods.index') }}" style="display: flex; gap: 6px; flex-wrap: wrap;">
        <input type="text" name="search" class="form-control" placeholder="Search dish..." value="{{ request('search') }}" style="width: 180px;">
        <select name="category_id" class="form-select" style="width: 160px;">
            <option value="">All Categories</option>
            @foreach($categories as $cat)
                <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
            @endforeach
        </select>
        <select name="status" class="form-select" style="width: 130px;">
            <option value="">All Statuses</option>
            <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Published</option>
            <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
            <option value="archived" {{ request('status') == 'archived' ? 'selected' : '' }}>Archived</option>
        </select>
        <button type="submit" class="btn btn-secondary"><i class="bi bi-funnel"></i> Filter</button>
        @if(request()->hasAny(['search', 'category_id', 'status']))
            <a href="{{ route('admin.foods.index') }}" class="btn btn-secondary"><i class="bi bi-x-circle"></i> Reset</a>
        @endif
    </form>

    <a href="{{ route('admin.foods.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Add New Dish</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="simple-table">
            <thead>
                <tr>
                    <th style="width: 60px;">Image</th>
                    <th>Dish Name</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Prep Time</th>
                    <th>Availability</th>
                    <th>Status</th>
                    <th style="text-align: right; width: 140px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($dishes as $dish)
                    <tr>
                        <td>
                            <img src="{{ $dish->cover_image ?: 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=120' }}" alt="" style="width: 48px; height: 48px; object-fit: cover; border-radius: var(--radius-sm);">
                        </td>
                        <td>
                            <strong>{{ $dish->name }}</strong>
                            <div style="font-size: 11px; color: var(--text-muted);">
                                @if($dish->is_chef_special) <span class="badge badge-pending" style="font-size: 9px;"><i class="bi bi-star-fill"></i> Special</span> @endif
                                @if($dish->is_vegetarian) <span class="badge badge-confirmed" style="font-size: 9px;"><i class="bi bi-flower1"></i> Veg</span> @endif
                                @if($dish->is_spicy) <span class="badge badge-cancelled" style="font-size: 9px;"><i class="bi bi-fire"></i> Spicy</span> @endif
                            </div>
                        </td>
                        <td>{{ $dish->category->name }}</td>
                        <td>
                            @if($dish->discount_price)
                                <strong>${{ number_format($dish->discount_price, 2) }}</strong>
                                <span style="text-decoration: line-through; font-size: 11px; color: var(--text-muted);">${{ number_format($dish->price, 2) }}</span>
                            @else
                                <strong>${{ number_format($dish->price, 2) }}</strong>
                            @endif
                        </td>
                        <td>{{ $dish->preparation_time }}m</td>
                        <td>
                            @if($dish->is_available)
                                <span class="badge badge-active">Available</span>
                            @else
                                <span class="badge badge-inactive">Sold Out</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-{{ $dish->status == 'published' ? 'active' : 'inactive' }}">{{ ucfirst($dish->status) }}</span>
                        </td>
                        <td style="text-align: right;">
                            <div style="display: flex; gap: 4px; justify-content: flex-end;">
                                <a href="{{ route('admin.foods.edit', $dish->id) }}" class="btn btn-secondary btn-sm"><i class="bi bi-pencil"></i> Edit</a>
                                <form method="POST" action="{{ route('admin.foods.destroy', $dish->id) }}" onsubmit="return confirm('Delete dish {{ $dish->name }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i> Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colSpan="8" style="text-align: center; padding: 20px; color: var(--text-muted);">No dishes found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 18px; flex-wrap: wrap; gap: 10px; font-size: 13px; color: var(--text-muted);">
        <div>
            Showing <strong>{{ $dishes->firstItem() ?? 0 }}</strong> to <strong>{{ $dishes->lastItem() ?? 0 }}</strong> of <strong>{{ $dishes->total() }}</strong> dishes
        </div>
        <div>
            {{ $dishes->links() }}
        </div>
    </div>
</div>
@endsection
