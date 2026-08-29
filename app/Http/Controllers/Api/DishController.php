<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Dish;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DishController extends Controller
{
    /**
     * Display a paginated listing of dishes with filters.
     */
    public function index(Request $request)
    {
        $query = Dish::with('category')->published();

        // Search query
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('short_description', 'like', "%{$search}%");
            });
        }

        // Category filter
        if ($categoryId = $request->input('category_id')) {
            $query->where('category_id', $categoryId);
        }

        if ($categorySlug = $request->input('category_slug')) {
            $query->whereHas('category', function ($q) use ($categorySlug) {
                $q->where('slug', $categorySlug);
            });
        }

        // Dietary & Chef Specials filter
        if ($request->boolean('is_vegetarian')) {
            $query->where('is_vegetarian', true);
        }

        if ($request->boolean('is_spicy')) {
            $query->where('is_spicy', true);
        }

        if ($request->boolean('is_chef_special')) {
            $query->where('is_chef_special', true);
        }

        // Price range
        if ($maxPrice = $request->input('max_price')) {
            $query->where('price', '<=', $maxPrice);
        }

        // Rating filter
        if ($minRating = $request->input('min_rating')) {
            $query->where('average_rating', '>=', $minRating);
        }

        // Sorting
        $sort = $request->input('sort', 'latest');
        switch ($sort) {
            case 'price_low':
                $query->orderBy('price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('price', 'desc');
                break;
            case 'rating':
                $query->orderBy('average_rating', 'desc');
                break;
            case 'popular':
                $query->orderBy('reviews_count', 'desc');
                break;
            default:
                $query->latest();
                break;
        }

        $perPage = (int) $request->input('per_page', 12);
        $dishes = $query->paginate($perPage);

        // Check favorite if user authenticated
        if ($user = $request->user('sanctum')) {
            $favDishIds = $user->favorites()->pluck('dish_id')->toArray();
            $dishes->getCollection()->transform(function ($dish) use ($favDishIds) {
                $dish->is_favorited = in_array($dish->id, $favDishIds, true);
                return $dish;
            });
        }

        return response()->json([
            'success' => true,
            'data' => $dishes->items(),
            'meta' => [
                'current_page' => $dishes->currentPage(),
                'last_page' => $dishes->lastPage(),
                'per_page' => $dishes->perPage(),
                'total' => $dishes->total(),
            ],
        ]);
    }

    /**
     * Get featured chef special dishes for homepage.
     */
    public function featured(Request $request)
    {
        $dishes = Dish::with('category')
            ->chefSpecials()
            ->take(6)
            ->get();

        if ($user = $request->user('sanctum')) {
            $favDishIds = $user->favorites()->pluck('dish_id')->toArray();
            $dishes->transform(function ($dish) use ($favDishIds) {
                $dish->is_favorited = in_array($dish->id, $favDishIds, true);
                return $dish;
            });
        }

        return response()->json([
            'success' => true,
            'data' => $dishes,
        ]);
    }

    /**
     * Display a specific dish by slug or ID.
     */
    public function show(Request $request, string $slug)
    {
        $dish = Dish::with(['category', 'reviews.user'])
            ->where('slug', $slug)
            ->orWhere('id', $slug)
            ->firstOrFail();

        if ($user = $request->user('sanctum')) {
            $dish->is_favorited = $user->favorites()->where('dish_id', $dish->id)->exists();
        }

        return response()->json([
            'success' => true,
            'data' => $dish,
        ]);
    }

    /**
     * Store a newly created dish (Admin only).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'short_description' => 'nullable|string|max:255',
            'price' => 'required|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0',
            'preparation_time' => 'nullable|integer|min:1',
            'calories' => 'nullable|integer|min:0',
            'is_spicy' => 'boolean',
            'is_vegetarian' => 'boolean',
            'is_chef_special' => 'boolean',
            'is_available' => 'boolean',
            'cover_image' => 'nullable|url',
            'images' => 'nullable|array',
            'status' => 'in:published,draft,archived',
        ]);

        $validated['slug'] = Str::slug($validated['name']) . '-' . Str::random(4);

        $dish = Dish::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Dish created successfully',
            'data' => $dish->load('category'),
        ], 201);
    }

    /**
     * Update an existing dish (Admin only).
     */
    public function update(Request $request, Dish $dish)
    {
        $validated = $request->validate([
            'category_id' => 'sometimes|required|exists:categories,id',
            'name' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'short_description' => 'nullable|string|max:255',
            'price' => 'sometimes|required|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0',
            'preparation_time' => 'nullable|integer|min:1',
            'calories' => 'nullable|integer|min:0',
            'is_spicy' => 'boolean',
            'is_vegetarian' => 'boolean',
            'is_chef_special' => 'boolean',
            'is_available' => 'boolean',
            'cover_image' => 'nullable|url',
            'images' => 'nullable|array',
            'status' => 'in:published,draft,archived',
        ]);

        $dish->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Dish updated successfully',
            'data' => $dish->fresh()->load('category'),
        ]);
    }

    /**
     * Remove the specified dish from storage (Admin only).
     */
    public function destroy(Dish $dish)
    {
        $dish->delete();

        return response()->json([
            'success' => true,
            'message' => 'Dish deleted successfully',
        ]);
    }
}
