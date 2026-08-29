<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Order;
use Illuminate\Http\Request;

class AdminOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['items.dish', 'user', 'payment'])->latest();

        if ($status = $request->input('status')) {
            $query->where('order_status', $status);
        }

        if ($orderType = $request->input('order_type')) {
            $query->where('order_type', $orderType);
        }

        if ($paymentStatus = $request->input('payment_status')) {
            $query->where('payment_status', $paymentStatus);
        }

        if ($date = $request->input('date')) {
            $query->whereDate('created_at', $date);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_phone', 'like', "%{$search}%");
            });
        }

        $orders = $query->paginate(15)->withQueryString();

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load(['items.dish', 'user', 'payment']);
        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'order_status' => 'required|in:pending,confirmed,preparing,ready,out_for_delivery,delivered,completed,cancelled',
            'payment_status' => 'nullable|in:pending,paid,refunded,failed',
        ]);

        $oldStatus = $order->order_status;
        $order->update($validated);

        if ($order->payment && !empty($validated['payment_status'])) {
            $order->payment->update([
                'status' => $validated['payment_status'],
                'paid_at' => $validated['payment_status'] === 'paid' ? ($order->payment->paid_at ?: now()) : $order->payment->paid_at,
            ]);
        }

        ActivityLog::log('order_status_update', "Updated Order #{$order->order_number} status from {$oldStatus} to {$validated['order_status']}.", $order);

        return back()->with('success', "Order #{$order->order_number} status updated to " . ucfirst($order->order_status) . '.');
    }

    public function print(Order $order)
    {
        $order->load(['items.dish', 'user', 'payment']);
        return view('admin.orders.print', compact('order'));
    }
}
