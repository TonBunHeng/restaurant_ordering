<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Dish;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Reservation;
use App\Models\RestaurantTable;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $today = now()->format('Y-m-d');

        $todaysOrdersCount = Order::whereDate('created_at', $today)->count();
        $todaysRevenue = Order::whereDate('created_at', $today)->where('payment_status', 'paid')->sum('total_amount');
        
        $todaysReservationsCount = Reservation::where('reservation_date', $today)->count();
        
        $availableTablesCount = RestaurantTable::where('status', 'available')->count();
        $occupiedTablesCount = RestaurantTable::where('status', 'occupied')->count();
        $totalTablesCount = RestaurantTable::count();

        $pendingOrdersCount = Order::where('order_status', 'pending')->count();
        $activeKitchenOrdersCount = Order::whereIn('order_status', ['confirmed', 'preparing', 'ready'])->count();
        $pendingReservationsCount = Reservation::where('status', 'pending')->count();

        $recentOrders = Order::with('user')->latest()->take(6)->get();
        $recentReservations = Reservation::with('table')->latest('reservation_date')->take(6)->get();

        $popularDishes = OrderItem::select('dish_id', 'dish_name', DB::raw('SUM(quantity) as total_qty'))
            ->groupBy('dish_id', 'dish_name')
            ->orderBy('total_qty', 'desc')
            ->take(5)
            ->get();

        $recentActivities = ActivityLog::with('user')->latest()->take(6)->get();

        return view('admin.dashboard', compact(
            'todaysOrdersCount',
            'todaysRevenue',
            'todaysReservationsCount',
            'availableTablesCount',
            'occupiedTablesCount',
            'totalTablesCount',
            'pendingOrdersCount',
            'activeKitchenOrdersCount',
            'pendingReservationsCount',
            'recentOrders',
            'recentReservations',
            'popularDishes',
            'recentActivities'
        ));
    }
}
