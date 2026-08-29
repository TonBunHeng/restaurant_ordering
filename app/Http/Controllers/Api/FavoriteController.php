<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Dish;
use App\Models\Favorite;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    /**
     * List user's favorite dishes.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $dishes = $user->favoriteDishes()->with('category')->paginate(12);

        $dishes->getCollection()->transform(function ($dish) {
            $dish->is_favorited = true;
            return $dish;
        });

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
     * Toggle favorite status for a dish.
     */
    public function toggle(Request $request, $dishId)
    {
        $dish = Dish::findOrFail($dishId);
        $user = $request->user();

        $favorite = Favorite::where('user_id', $user->id)
            ->where('dish_id', $dish->id)
            ->first();

        if ($favorite) {
            $favorite->delete();
            $isFavorited = false;
            $message = 'Removed from favorite dishes';
        } else {
            Favorite::create([
                'user_id' => $user->id,
                'dish_id' => $dish->id,
            ]);
            $isFavorited = true;
            $message = 'Added to favorite dishes';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => [
                'dish_id' => $dish->id,
                'is_favorited' => $isFavorited,
            ],
        ]);
    }
}
