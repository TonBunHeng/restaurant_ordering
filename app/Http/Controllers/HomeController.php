<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Dish;
use App\Models\RestaurantTable;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('publishedDishes')
            ->where('is_active', true)
            ->orderBy('order', 'asc')
            ->get();

        $featuredDishes = Dish::with('category')
            ->where('status', 'published')
            ->where('is_available', true)
            ->where('is_chef_special', true)
            ->take(6)
            ->get();

        if ($featuredDishes->isEmpty()) {
            $featuredDishes = Dish::with('category')
                ->where('status', 'published')
                ->where('is_available', true)
                ->take(6)
                ->get();
        }

        $availableTablesCount = RestaurantTable::available()->count();

        return view('home', compact('categories', 'featuredDishes', 'availableTablesCount'));
    }
}
