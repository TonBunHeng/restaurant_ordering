<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Dish;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::where('is_active', true)->orderBy('order', 'asc')->get();

        $query = Dish::with('category')->where('status', 'published');

        // Only show available dishes unless admin or specified
        if ($request->has('available_only') ? $request->boolean('available_only') : true) {
            $query->where('is_available', true);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('short_description', 'like', "%{$search}%")
                  ->orWhere('ingredients', 'like', "%{$search}%");
            });
        }

        if ($categorySlug = $request->input('category')) {
            $query->whereHas('category', function ($q) use ($categorySlug) {
                $q->where('slug', $categorySlug);
            });
        }

        if ($request->boolean('is_vegetarian')) {
            $query->where('is_vegetarian', true);
        }

        if ($request->boolean('is_spicy')) {
            $query->where('is_spicy', true);
        }

        if ($request->boolean('is_halal')) {
            $query->where('is_halal', true);
        }

        if ($request->boolean('is_chef_special')) {
            $query->where('is_chef_special', true);
        }

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
        }

        $dishes = $query->paginate(12)->withQueryString();

        return view('menu.index', compact('categories', 'dishes'));
    }

    public function show(string $slug)
    {
        $dish = Dish::with(['category', 'reviews.user'])
            ->where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        $relatedDishes = Dish::with('category')
            ->where('category_id', $dish->category_id)
            ->where('id', '!=', $dish->id)
            ->where('status', 'published')
            ->where('is_available', true)
            ->take(4)
            ->get();

        // Check if current user has ordered this dish and can review
        $canReview = false;
        if (auth()->check()) {
            $canReview = auth()->user()->orders()
                ->whereIn('order_status', ['completed', 'delivered'])
                ->whereHas('items', function ($q) use ($dish) {
                    $q->where('dish_id', $dish->id);
                })
                ->exists();
        }

        $isFavorite = false;
        if (auth()->check()) {
            $isFavorite = auth()->user()->favorites()->where('dish_id', $dish->id)->exists();
        }

        return view('menu.show', compact('dish', 'relatedDishes', 'canReview', 'isFavorite'));
    }
}
