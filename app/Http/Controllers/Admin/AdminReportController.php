<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dish;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminReportController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->input('tab', 'sales');

        // Date range filters for Sales Report
        $period = $request->input('period', 'this_month');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        switch ($period) {
            case 'today':
                $start = now()->startOfDay();
                $end = now()->endOfDay();
                break;
            case 'yesterday':
                $start = now()->subDay()->startOfDay();
                $end = now()->subDay()->endOfDay();
                break;
            case 'this_week':
                $start = now()->startOfWeek();
                $end = now()->endOfWeek();
                break;
            case 'this_month':
                $start = now()->startOfMonth();
                $end = now()->endOfMonth();
                break;
            case 'custom':
                $start = $startDate ? Carbon::parse($startDate)->startOfDay() : now()->startOfMonth();
                $end = $endDate ? Carbon::parse($endDate)->endOfDay() : now()->endOfMonth();
                break;
            default:
                $start = now()->startOfMonth();
                $end = now()->endOfMonth();
        }

        // 1. Sales Report Data
        $salesQuery = Order::whereBetween('created_at', [$start, $end]);
        $totalOrders = (clone $salesQuery)->count();
        $totalRevenue = (clone $salesQuery)->where('payment_status', 'paid')->sum('total_amount');
        $completedOrders = (clone $salesQuery)->whereIn('order_status', ['completed', 'delivered'])->count();
        $cancelledOrders = (clone $salesQuery)->where('order_status', 'cancelled')->count();
        $avgOrderValue = $totalOrders > 0 ? $totalRevenue / max(1, (clone $salesQuery)->where('payment_status', 'paid')->count()) : 0;

        $ordersList = (clone $salesQuery)->with(['items', 'user'])->latest()->take(20)->get();

        // 2. Food Report Data
        $popularDishes = OrderItem::select('dish_id', 'dish_name', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(subtotal_price) as total_sales'))
            ->groupBy('dish_id', 'dish_name')
            ->orderBy('total_qty', 'desc')
            ->take(10)
            ->get();

        $leastOrderedDishes = Dish::where('status', 'published')
            ->withCount('orderItems')
            ->orderBy('order_items_count', 'asc')
            ->take(10)
            ->get();

        // 3. Reservation Report Data
        $resQuery = Reservation::whereBetween('reservation_date', [$start->format('Y-m-d'), $end->format('Y-m-d')]);
        $totalReservations = (clone $resQuery)->count();
        $confirmedReservations = (clone $resQuery)->where('status', 'confirmed')->count();
        $completedReservations = (clone $resQuery)->where('status', 'completed')->count();
        $cancelledReservations = (clone $resQuery)->where('status', 'cancelled')->count();
        $rejectedReservations = (clone $resQuery)->where('status', 'rejected')->count();
        $pendingReservations = (clone $resQuery)->where('status', 'pending')->count();

        return view('admin.reports.index', compact(
            'tab',
            'period',
            'start',
            'end',
            'totalOrders',
            'totalRevenue',
            'completedOrders',
            'cancelledOrders',
            'avgOrderValue',
            'ordersList',
            'popularDishes',
            'leastOrderedDishes',
            'totalReservations',
            'confirmedReservations',
            'completedReservations',
            'cancelledReservations',
            'rejectedReservations',
            'pendingReservations'
        ));
    }
}
