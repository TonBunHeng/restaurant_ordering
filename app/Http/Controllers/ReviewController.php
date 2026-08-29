<?php

namespace App\Http\Controllers;

use App\Models\Dish;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request, Dish $dish)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|min:3|max:1000',
        ]);

        // Optional check: verify user has ordered this dish from a completed order
        $hasOrdered = $user->orders()
            ->whereIn('order_status', ['completed', 'delivered'])
            ->whereHas('items', function ($q) use ($dish) {
                $q->where('dish_id', $dish->id);
            })
            ->exists();

        // Create or update review
        $review = Review::updateOrCreate(
            [
                'user_id' => $user->id,
                'dish_id' => $dish->id,
            ],
            [
                'rating' => $validated['rating'],
                'comment' => $validated['comment'],
                'status' => 'published',
            ]
        );

        $dish->refreshRatingStats();

        return back()->with('success', 'Thank you! Your review for ' . $dish->name . ' has been published.');
    }
}
