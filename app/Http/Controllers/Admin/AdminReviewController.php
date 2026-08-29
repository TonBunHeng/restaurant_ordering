<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Review;
use Illuminate\Http\Request;

class AdminReviewController extends Controller
{
    public function index(Request $request)
    {
        $query = Review::with(['user', 'dish'])->latest();

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($rating = $request->input('rating')) {
            $query->where('rating', $rating);
        }

        $reviews = $query->paginate(15)->withQueryString();

        return view('admin.reviews.index', compact('reviews'));
    }

    public function updateStatus(Request $request, Review $review)
    {
        $validated = $request->validate([
            'status' => 'required|in:published,hidden,rejected',
        ]);

        $review->update(['status' => $validated['status']]);

        if ($review->dish) {
            $review->dish->refreshRatingStats();
        }

        ActivityLog::log('review_status_updated', "Updated review #{$review->id} status to {$validated['status']}.", $review);

        return back()->with('success', 'Review status updated successfully.');
    }

    public function destroy(Review $review)
    {
        $dish = $review->dish;
        ActivityLog::log('review_deleted', "Deleted review #{$review->id} by {$review->user?->name}.", $review);

        $review->delete();

        if ($dish) {
            $dish->refreshRatingStats();
        }

        return back()->with('success', 'Review deleted successfully.');
    }
}
