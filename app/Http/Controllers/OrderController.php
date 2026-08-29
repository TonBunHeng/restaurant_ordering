<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index()
    {
        $orders = auth()->user()->orders()->with('items.dish')->latest()->paginate(10);

        return view('orders.index', compact('orders'));
    }

    public function show(string $identifier)
    {
        $order = Order::with(['items.dish', 'payment', 'user'])
            ->where(function ($q) use ($identifier) {
                if (is_numeric($identifier)) {
                    $q->where('id', (int) $identifier);
                } else {
                    $q->where('order_number', $identifier);
                }
            })
            ->firstOrFail();

        $currentUser = auth()->user();

        // Enforce ownership: customers can only view their own orders
        if ($order->user_id && (!$currentUser || ($order->user_id !== $currentUser->id && !$currentUser->isAdmin()))) {
            abort(403, 'Unauthorized access to this order receipt.');
        }

        return view('orders.show', compact('order'));
    }

    public function cancel(Order $order)
    {
        $currentUser = auth()->user();

        if ($order->user_id !== $currentUser->id && !$currentUser->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        if ($order->order_status !== 'pending') {
            return back()->with('error', 'Orders can only be cancelled while in Pending status before kitchen preparation begins.');
        }

        $order->update([
            'order_status' => 'cancelled',
        ]);

        return back()->with('success', "Order #{$order->order_number} has been cancelled.");
    }
}
