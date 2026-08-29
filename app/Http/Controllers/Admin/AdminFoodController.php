<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Dish;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminFoodController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::orderBy('name', 'asc')->get();

        $query = Dish::with('category')->latest();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('ingredients', 'like', "%{$search}%");
            });
        }

        if ($categoryId = $request->input('category_id')) {
            $query->where('category_id', $categoryId);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $dishes = $query->paginate(15)->withQueryString();

        return view('admin.foods.index', compact('dishes', 'categories'));
    }

    public function create()
    {
        $categories = Category::where('is_active', true)->orderBy('name', 'asc')->get();
        return view('admin.foods.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'short_description' => 'nullable|string|max:255',
            'description' => 'required|string',
            'ingredients' => 'nullable|string|max:1000',
            'allergens' => 'nullable|string|max:255',
            'dietary_info' => 'nullable|string|max:255',
            'price' => 'required|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0|lt:price',
            'preparation_time' => 'required|integer|min:1',
            'calories' => 'nullable|integer|min:0',
            'spicy_level' => 'nullable|integer|min:0|max:5',
            'cover_image' => 'nullable|string|max:500',
            'image_file' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'is_spicy' => 'nullable|boolean',
            'is_vegetarian' => 'nullable|boolean',
            'is_halal' => 'nullable|boolean',
            'is_chef_special' => 'nullable|boolean',
            'is_available' => 'nullable|boolean',
            'status' => 'required|in:published,draft,archived',
        ]);

        if ($request->hasFile('image_file')) {
            $file = $request->file('image_file');
            $filename = 'dish_' . Str::random(12) . '.' . $file->getClientOriginalExtension();
            $dest = public_path('uploads/dishes');
            if (!file_exists($dest)) {
                @mkdir($dest, 0755, true);
            }
            $file->move($dest, $filename);
            $validated['cover_image'] = '/uploads/dishes/' . $filename;
        }

        $validated['slug'] = Str::slug($validated['name']) . '-' . Str::random(5);
        $validated['is_spicy'] = $request->boolean('is_spicy');
        $validated['is_vegetarian'] = $request->boolean('is_vegetarian');
        $validated['is_halal'] = $request->boolean('is_halal');
        $validated['is_chef_special'] = $request->boolean('is_chef_special');
        $validated['is_available'] = $request->boolean('is_available', true);
        $validated['spicy_level'] = (int) ($validated['spicy_level'] ?? ($validated['is_spicy'] ? 2 : 0));

        $dish = Dish::create($validated);

        ActivityLog::log('dish_created', "Created food item '{$dish->name}' (\${$dish->price}).", $dish);

        return redirect()->route('admin.foods.index')->with('success', 'Food item created successfully.');
    }

    public function edit(Dish $food)
    {
        $categories = Category::orderBy('name', 'asc')->get();
        return view('admin.foods.edit', compact('food', 'categories'));
    }

    public function update(Request $request, Dish $food)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'short_description' => 'nullable|string|max:255',
            'description' => 'required|string',
            'ingredients' => 'nullable|string|max:1000',
            'allergens' => 'nullable|string|max:255',
            'dietary_info' => 'nullable|string|max:255',
            'price' => 'required|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0|lt:price',
            'preparation_time' => 'required|integer|min:1',
            'calories' => 'nullable|integer|min:0',
            'spicy_level' => 'nullable|integer|min:0|max:5',
            'cover_image' => 'nullable|string|max:500',
            'image_file' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'is_spicy' => 'nullable|boolean',
            'is_vegetarian' => 'nullable|boolean',
            'is_halal' => 'nullable|boolean',
            'is_chef_special' => 'nullable|boolean',
            'is_available' => 'nullable|boolean',
            'status' => 'required|in:published,draft,archived',
        ]);

        if ($request->hasFile('image_file')) {
            $file = $request->file('image_file');
            $filename = 'dish_' . Str::random(12) . '.' . $file->getClientOriginalExtension();
            $dest = public_path('uploads/dishes');
            if (!file_exists($dest)) {
                @mkdir($dest, 0755, true);
            }
            $file->move($dest, $filename);
            $validated['cover_image'] = '/uploads/dishes/' . $filename;
        }

        $validated['is_spicy'] = $request->boolean('is_spicy');
        $validated['is_vegetarian'] = $request->boolean('is_vegetarian');
        $validated['is_halal'] = $request->boolean('is_halal');
        $validated['is_chef_special'] = $request->boolean('is_chef_special');
        $validated['is_available'] = $request->boolean('is_available');
        $validated['spicy_level'] = (int) ($validated['spicy_level'] ?? ($validated['is_spicy'] ? 2 : 0));

        $food->update($validated);

        ActivityLog::log('dish_updated', "Updated food item '{$food->name}'.", $food);

        return redirect()->route('admin.foods.index')->with('success', 'Food item updated successfully.');
    }

    public function destroy(Dish $food)
    {
        ActivityLog::log('dish_deleted', "Deleted food item '{$food->name}'.", $food);
        $food->delete();

        return redirect()->route('admin.foods.index')->with('success', 'Food item deleted successfully.');
    }
}
