<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Order;
use Illuminate\Http\Request;

class AdminKitchenController extends Controller
{
    public function index()
    {
        $activeOrders = Order::with(['items.dish', 'user'])
            ->whereIn('order_status', ['pending', 'confirmed', 'preparing', 'ready'])
            ->orderBy('created_at', 'asc')
            ->get();

        return view('admin.kitchen.index', compact('activeOrders'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'order_status' => 'required|in:pending,confirmed,preparing,ready,completed,cancelled',
        ]);

        $oldStatus = $order->order_status;
        $order->update(['order_status' => $validated['order_status']]);

        ActivityLog::log(
            'kitchen_status_update',
            "Kitchen updated Order #{$order->order_number} status from {$oldStatus} to {$validated['order_status']}.",
            $order
        );

        return back()->with('success', "Order #{$order->order_number} moved to " . ucfirst($validated['order_status']) . '.');
    }
}
