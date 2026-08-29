@extends('layouts.admin')

@section('title', 'Edit Category — Admin')
@section('page-title', 'Edit Food Category: ' . $category->name)

@section('content')
<div style="max-width: 600px;">
    <div class="card">
        <form method="POST" action="{{ route('admin.categories.update', $category->id) }}">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label class="form-label" for="name">Category Name *</label>
                <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $category->name) }}" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="description">Description</label>
                <textarea id="description" name="description" class="form-control" rows="2">{{ old('description', $category->description) }}</textarea>
            </div>

            <div class="grid grid-2">
                <div class="form-group">
                    <label class="form-label" for="order">Display Order (Sort Priority)</label>
                    <input type="number" id="order" name="order" class="form-control" value="{{ old('order', $category->order) }}">
                </div>

                <div class="form-group">
                    <label class="form-label" for="icon">Icon / Tag (Optional)</label>
                    <input type="text" id="icon" name="icon" class="form-control" value="{{ old('icon', $category->icon) }}">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="image">Cover Image URL (Optional)</label>
                <input type="url" id="image" name="image" class="form-control" value="{{ old('image', $category->image) }}">
            </div>

            <div class="form-group">
                <label class="form-check">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $category->is_active) ? 'checked' : '' }}>
                    <span>Category is Active (Visible to Customers)</span>
                </label>
            </div>

            <div style="margin-top: 20px; display: flex; gap: 10px;">
                <button type="submit" class="btn btn-primary">Update Category</button>
                <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
