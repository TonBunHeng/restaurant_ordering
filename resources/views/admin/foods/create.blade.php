@extends('layouts.admin')

@section('title', 'Add Food Dish — Admin')
@section('page-title', 'Create New Menu Dish')

@section('content')
<div style="max-width: 780px;">
    <div class="card">
        <form method="POST" action="{{ route('admin.foods.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="grid grid-2">
                <div class="form-group">
                    <label class="form-label" for="name">Dish Name *</label>
                    <input type="text" id="name" name="name" class="form-control" value="{{ old('name') }}" required placeholder="e.g. Royal Steamed Fish Amok">
                </div>

                <div class="form-group">
                    <label class="form-label" for="category_id">Category *</label>
                    <select id="category_id" name="category_id" class="form-select" required>
                        <option value="">Select Category</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-3">
                <div class="form-group">
                    <label class="form-label" for="price">Regular Price ($) *</label>
                    <input type="number" step="0.01" id="price" name="price" class="form-control" value="{{ old('price') }}" required placeholder="12.50">
                </div>

                <div class="form-group">
                    <label class="form-label" for="discount_price">Discount Price ($)</label>
                    <input type="number" step="0.01" id="discount_price" name="discount_price" class="form-control" value="{{ old('discount_price') }}" placeholder="10.99">
                </div>

                <div class="form-group">
                    <label class="form-label" for="preparation_time">Prep Time (mins) *</label>
                    <input type="number" id="preparation_time" name="preparation_time" class="form-control" value="{{ old('preparation_time', 15) }}" required min="1">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="short_description">Short Summary</label>
                <input type="text" id="short_description" name="short_description" class="form-control" value="{{ old('short_description') }}" placeholder="One-line summary for menu cards...">
            </div>

            <div class="form-group">
                <label class="form-label" for="description">Full Description *</label>
                <textarea id="description" name="description" class="form-control" rows="3" required placeholder="Detailed ingredients, taste profile, and preparation style...">{{ old('description') }}</textarea>
            </div>

            <div class="grid grid-2">
                <div class="form-group">
                    <label class="form-label" for="ingredients">Key Ingredients</label>
                    <input type="text" id="ingredients" name="ingredients" class="form-control" value="{{ old('ingredients') }}" placeholder="e.g. River fish, kroeung paste, coconut milk, kaffir lime">
                </div>

                <div class="form-group">
                    <label class="form-label" for="allergens">Allergens</label>
                    <input type="text" id="allergens" name="allergens" class="form-control" value="{{ old('allergens') }}" placeholder="e.g. Fish, peanuts, shellfish (or None)">
                </div>
            </div>

            <div class="grid grid-3">
                <div class="form-group">
                    <label class="form-label" for="calories">Calories (kcal)</label>
                    <input type="number" id="calories" name="calories" class="form-control" value="{{ old('calories') }}" placeholder="350">
                </div>

                <div class="form-group">
                    <label class="form-label" for="spicy_level">Spicy Level (0 - 5)</label>
                    <input type="number" id="spicy_level" name="spicy_level" class="form-control" value="{{ old('spicy_level', 0) }}" min="0" max="5">
                </div>

                <div class="form-group">
                    <label class="form-label" for="status">Publication Status *</label>
                    <select id="status" name="status" class="form-select" required>
                        <option value="published" {{ old('status', 'published') == 'published' ? 'selected' : '' }}>Published</option>
                        <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Draft (Hidden)</option>
                        <option value="archived" {{ old('status') == 'archived' ? 'selected' : '' }}>Archived</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-2">
                <div class="form-group">
                    <label class="form-label" for="cover_image">Cover Image URL</label>
                    <input type="text" id="cover_image" name="cover_image" class="form-control" value="{{ old('cover_image') }}" placeholder="https://images.unsplash.com/...">
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
                        <input type="checkbox" name="is_available" value="1" {{ old('is_available', true) ? 'checked' : '' }}>
                        <span>Available for Ordering</span>
                    </label>
                    <label class="form-check">
                        <input type="checkbox" name="is_chef_special" value="1" {{ old('is_chef_special') ? 'checked' : '' }}>
                        <span>Chef Special</span>
                    </label>
                    <label class="form-check">
                        <input type="checkbox" name="is_vegetarian" value="1" {{ old('is_vegetarian') ? 'checked' : '' }}>
                        <span>Vegetarian</span>
                    </label>
                    <label class="form-check">
                        <input type="checkbox" name="is_halal" value="1" {{ old('is_halal') ? 'checked' : '' }}>
                        <span>Halal</span>
                    </label>
                    <label class="form-check">
                        <input type="checkbox" name="is_spicy" value="1" {{ old('is_spicy') ? 'checked' : '' }}>
                        <span>Spicy</span>
                    </label>
                </div>
            </div>

            <div style="display: flex; gap: 10px;">
                <button type="submit" class="btn btn-primary">Save Food Item</button>
                <a href="{{ route('admin.foods.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
