@extends('layouts.admin')

@section('title', 'Edit Food Dish — Admin')
@section('page-title', 'Edit Menu Dish: ' . $food->name)

@section('content')
<div style="max-width: 780px;">
    <div class="card">
        <form method="POST" action="{{ route('admin.foods.update', $food->id) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="grid grid-2">
                <div class="form-group">
                    <label class="form-label" for="name">Dish Name *</label>
                    <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $food->name) }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="category_id">Category *</label>
                    <select id="category_id" name="category_id" class="form-select" required>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id', $food->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-3">
                <div class="form-group">
                    <label class="form-label" for="price">Regular Price ($) *</label>
                    <input type="number" step="0.01" id="price" name="price" class="form-control" value="{{ old('price', $food->price) }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="discount_price">Discount Price ($)</label>
                    <input type="number" step="0.01" id="discount_price" name="discount_price" class="form-control" value="{{ old('discount_price', $food->discount_price) }}">
                </div>

                <div class="form-group">
                    <label class="form-label" for="preparation_time">Prep Time (mins) *</label>
                    <input type="number" id="preparation_time" name="preparation_time" class="form-control" value="{{ old('preparation_time', $food->preparation_time) }}" required min="1">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="short_description">Short Summary</label>
                <input type="text" id="short_description" name="short_description" class="form-control" value="{{ old('short_description', $food->short_description) }}">
            </div>

            <div class="form-group">
                <label class="form-label" for="description">Full Description *</label>
                <textarea id="description" name="description" class="form-control" rows="3" required>{{ old('description', $food->description) }}</textarea>
            </div>

            <div class="grid grid-2">
                <div class="form-group">
                    <label class="form-label" for="ingredients">Key Ingredients</label>
                    <input type="text" id="ingredients" name="ingredients" class="form-control" value="{{ old('ingredients', $food->ingredients) }}">
                </div>

                <div class="form-group">
                    <label class="form-label" for="allergens">Allergens</label>
                    <input type="text" id="allergens" name="allergens" class="form-control" value="{{ old('allergens', $food->allergens) }}">
                </div>
            </div>

            <div class="grid grid-3">
                <div class="form-group">
                    <label class="form-label" for="calories">Calories (kcal)</label>
                    <input type="number" id="calories" name="calories" class="form-control" value="{{ old('calories', $food->calories) }}">
                </div>

                <div class="form-group">
                    <label class="form-label" for="spicy_level">Spicy Level (0 - 5)</label>
                    <input type="number" id="spicy_level" name="spicy_level" class="form-control" value="{{ old('spicy_level', $food->spicy_level) }}" min="0" max="5">
                </div>

                <div class="form-group">
                    <label class="form-label" for="status">Publication Status *</label>
                    <select id="status" name="status" class="form-select" required>
                        <option value="published" {{ old('status', $food->status) == 'published' ? 'selected' : '' }}>Published</option>
                        <option value="draft" {{ old('status', $food->status) == 'draft' ? 'selected' : '' }}>Draft (Hidden)</option>
                        <option value="archived" {{ old('status', $food->status) == 'archived' ? 'selected' : '' }}>Archived</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-2">
                <div class="form-group">
                    <label class="form-label" for="cover_image">Cover Image URL</label>
                    <input type="text" id="cover_image" name="cover_image" class="form-control" value="{{ old('cover_image', $food->cover_image) }}">
                </div>

                <div class="form-group">
                    <label class="form-label" for="image_file">Or Upload Image File (JPG, PNG, WebP max 2MB)</label>
                    <input type="file" id="image_file" name="image_file" class="form-control" accept="image/*">
                </div>
            </div>

            <div style="background: #f8fafc; padding: 14px; border-radius: var(--radius); border: 1px solid var(--border); margin-bottom: 20px;">
                <div style="font-weight: 600; font-size: 13px; margin-bottom: 8px;">Attributes & Dietary Badges:</div>
                <div style="display: flex; gap: 20px; flex-wrap: wrap;">
                    <label class="form-check">
                        <input type="checkbox" name="is_available" value="1" {{ old('is_available', $food->is_available) ? 'checked' : '' }}>
                        <span>Available for Ordering</span>
                    </label>
                    <label class="form-check">
                        <input type="checkbox" name="is_chef_special" value="1" {{ old('is_chef_special', $food->is_chef_special) ? 'checked' : '' }}>
                        <span>Chef Special</span>
                    </label>
                    <label class="form-check">
                        <input type="checkbox" name="is_vegetarian" value="1" {{ old('is_vegetarian', $food->is_vegetarian) ? 'checked' : '' }}>
                        <span>Vegetarian</span>
                    </label>
                    <label class="form-check">
                        <input type="checkbox" name="is_halal" value="1" {{ old('is_halal', $food->is_halal) ? 'checked' : '' }}>
                        <span>Halal</span>
                    </label>
                    <label class="form-check">
                        <input type="checkbox" name="is_spicy" value="1" {{ old('is_spicy', $food->is_spicy) ? 'checked' : '' }}>
                        <span>Spicy</span>
                    </label>
                </div>
            </div>

            <div style="display: flex; gap: 10px;">
                <button type="submit" class="btn btn-primary">Update Food Item</button>
                <a href="{{ route('admin.foods.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
