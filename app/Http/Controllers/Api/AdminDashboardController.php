<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Dish;
use App\Models\Category;
use App\Models\Order;
use App\Models\Reservation;
use App\Models\Review;
use App\Models\User;
use App\Models\Conversation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    /**
     * Get aggregate statistics for the Restaurant Admin Dashboard.
     */
    public function stats(Request $request)
    {
        $totalSales = (float) Order::where('payment_status', 'paid')->orWhere('order_status', 'delivered')->sum('total_amount');
        $totalOrders = Order::count();
        $pendingOrders = Order::where('order_status', 'pending')->count();
        $totalReservations = Reservation::count();
        $pendingReservations = Reservation::where('status', 'pending')->count();
        $totalDishes = Dish::count();
        $totalCategories = Category::count();
        $totalUsers = User::count();
        $totalConversations = Conversation::count();
        $averageRating = round((float) Review::where('status', 'published')->avg('rating') ?: 5.0, 2);

        // Recent orders
        $recentOrders = Order::with('items.dish')
            ->latest()
            ->take(5)
            ->get();

        // Recent table reservations
        $recentReservations = Reservation::latest('reservation_date')
            ->take(5)
            ->get();

        // Top selling / highest rated dishes
        $topDishes = Dish::with('category')
            ->published()
            ->orderBy('reviews_count', 'desc')
            ->take(5)
            ->get();

        // Orders breakdown by status
        $ordersByStatus = Order::select('order_status', DB::raw('count(*) as count'))
            ->groupBy('order_status')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'metrics' => [
                    'total_sales' => $totalSales,
                    'total_orders' => $totalOrders,
                    'pending_orders' => $pendingOrders,
                    'total_reservations' => $totalReservations,
                    'pending_reservations' => $pendingReservations,
                    'total_dishes' => $totalDishes,
                    'total_categories' => $totalCategories,
                    'total_users' => $totalUsers,
                    'total_conversations' => $totalConversations,
                    'average_system_rating' => $averageRating,
                ],
                'recent_orders' => $recentOrders,
                'recent_reservations' => $recentReservations,
                'top_dishes' => $topDishes,
                'orders_by_status' => $ordersByStatus,
            ],
        ]);
    }
}
