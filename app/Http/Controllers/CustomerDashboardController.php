<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Reservation;
use App\Models\Dish;
use App\Models\Review;
use Illuminate\Http\Request;

class CustomerDashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Active orders (pending, confirmed, preparing, out_for_delivery)
        $activeOrders = $user->orders()
            ->with('items.dish')
            ->whereIn('order_status', ['pending', 'confirmed', 'preparing', 'out_for_delivery'])
            ->latest()
            ->get();

        // Recent orders
        $recentOrders = $user->orders()
            ->with('items.dish')
            ->latest()
            ->take(5)
            ->get();

        // Upcoming reservations (pending, confirmed for today or future)
        $upcomingReservations = $user->reservations()
            ->with('table')
            ->whereIn('status', ['pending', 'confirmed'])
            ->where('reservation_date', '>=', now()->format('Y-m-d'))
            ->orderBy('reservation_date', 'asc')
            ->orderBy('reservation_time', 'asc')
            ->take(5)
            ->get();

        // Favorite dishes
        $favoriteDishes = $user->favoriteDishes()
            ->with('category')
            ->where('status', 'published')
            ->take(6)
            ->get();

        // Recent reviews
        $recentReviews = $user->reviews()
            ->with('dish')
            ->latest()
            ->take(5)
            ->get();

        return view('customer.dashboard', compact(
            'user',
            'activeOrders',
            'recentOrders',
            'upcomingReservations',
            'favoriteDishes',
            'recentReviews'
        ));
    }
}
