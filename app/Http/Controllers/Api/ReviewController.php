<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Dish;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /**
     * Display reviews for a dish.
     */
    public function index(Request $request, $dishId)
    {
        $reviews = Review::with('user:id,name,avatar')
            ->where('dish_id', $dishId)
            ->where('status', 'published')
            ->latest()
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $reviews->items(),
            'meta' => [
                'current_page' => $reviews->currentPage(),
                'last_page' => $reviews->lastPage(),
                'per_page' => $reviews->perPage(),
                'total' => $reviews->total(),
            ],
        ]);
    }

    /**
     * Store new review for a dish.
     */
    public function store(Request $request, $dishId)
    {
        $dish = Dish::findOrFail($dishId);
        $user = $request->user();

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'nullable|string|max:255',
            'comment' => 'required|string',
        ]);

        $review = Review::updateOrCreate(
            [
                'user_id' => $user->id,
                'dish_id' => $dish->id,
            ],
            [
                'rating' => $validated['rating'],
                'title' => $validated['title'] ?? null,
                'comment' => $validated['comment'],
                'status' => 'published',
            ]
        );

        $dish->refreshRatingStats();

        return response()->json([
            'success' => true,
            'message' => 'Review submitted successfully',
            'data' => $review->load('user:id,name,avatar'),
        ], 201);
    }

    /**
     * Delete review.
     */
    public function destroy(Request $request, Review $review)
    {
        $user = $request->user();

        if ($review->user_id !== $user->id && !$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action',
            ], 403);
        }

        $dish = $review->dish;
        $review->delete();
        $dish?->refreshRatingStats();

        return response()->json([
            'success' => true,
            'message' => 'Review deleted successfully',
        ]);
    }
}
