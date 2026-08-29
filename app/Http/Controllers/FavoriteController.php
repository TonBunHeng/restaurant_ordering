<?php

namespace App\Http\Controllers;

use App\Models\Dish;
use App\Models\Favorite;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function index()
    {
        $favorites = auth()->user()->favoriteDishes()->with('category')->paginate(12);
        return view('favorites.index', compact('favorites'));
    }

    public function toggle(Request $request, Dish $dish)
    {
        $user = auth()->user();
        $existing = Favorite::where('user_id', $user->id)->where('dish_id', $dish->id)->first();

        if ($existing) {
            $existing->delete();
            $status = 'removed';
            $msg = "'{$dish->name}' removed from your favorites.";
        } else {
            Favorite::create([
                'user_id' => $user->id,
                'dish_id' => $dish->id,
            ]);
            $status = 'added';
            $msg = "'{$dish->name}' added to your favorites!";
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'status' => $status,
                'message' => $msg,
            ]);
        }

        return back()->with('success', $msg);
    }
}
