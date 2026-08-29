<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Dish;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    /**
     * Create a new customer food order.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:50',
            'customer_email' => 'nullable|email',
            'delivery_address' => 'required|string',
            'notes' => 'nullable|string',
            'payment_method' => 'required|in:cash_on_delivery,aba_pay,credit_card,qr_payment',
            'items' => 'required|array|min:1',
            'items.*.dish_id' => 'required|exists:dishes,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.special_instructions' => 'nullable|string|max:255',
        ]);

        return DB::transaction(function () use ($validated, $request) {
            $subtotal = 0;
            $orderItemsData = [];

            foreach ($validated['items'] as $item) {
                $dish = Dish::findOrFail($item['dish_id']);

                if (!$dish->is_available || $dish->status !== 'published') {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'items' => ["The dish '{$dish->name}' is currently unavailable."],
                    ]);
                }

                $unitPrice = $dish->discount_price ?: $dish->price;
                $lineTotal = $unitPrice * $item['quantity'];
                $subtotal += $lineTotal;

                $orderItemsData[] = [
                    'dish_id' => $dish->id,
                    'dish_name' => $dish->name,
                    'quantity' => $item['quantity'],
                    'unit_price' => $unitPrice,
                    'subtotal_price' => $lineTotal,
                    'special_instructions' => $item['special_instructions'] ?? null,
                ];
            }

            $deliveryFee = $subtotal > 30.00 ? 0.00 : 2.00; // Free delivery over $30
            $totalAmount = $subtotal + $deliveryFee;

            $order = Order::create([
                'user_id' => $request->user('sanctum')?->id,
                'subtotal' => $subtotal,
                'delivery_fee' => $deliveryFee,
                'discount_amount' => 0.00,
                'total_amount' => $totalAmount,
                'payment_method' => $validated['payment_method'],
                'payment_status' => $validated['payment_method'] === 'cash_on_delivery' ? 'pending' : 'paid',
                'order_status' => 'pending',
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'],
                'customer_email' => $validated['customer_email'] ?? null,
                'delivery_address' => $validated['delivery_address'],
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($orderItemsData as $itemData) {
                $order->items()->create($itemData);
            }

            return response()->json([
                'success' => true,
                'message' => 'Order placed successfully!',
                'data' => $order->load(['items.dish']),
            ], 201);
        });
    }

    /**
     * Display listing of authenticated user's orders.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $orders = $user->orders()->with('items.dish')->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $orders->items(),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ],
        ]);
    }

    /**
     * Display a specific order by order_number or ID with authorization check.
     */
    public function show(Request $request, string $orderNumber)
    {
        $isNumericId = is_numeric($orderNumber);

        $order = Order::with('items.dish')
            ->when($isNumericId, function ($query) use ($orderNumber) {
                $query->where('id', (int) $orderNumber);
            }, function ($query) use ($orderNumber) {
                $query->where('order_number', $orderNumber);
            })
            ->firstOrFail();

        $currentUser = $request->user('sanctum');

        // If queried by numeric ID, enforce that user must be authenticated and own the order (or be admin)
        if ($isNumericId) {
            if (!$currentUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated.',
                    'errors' => ['auth' => ['Please log in to view this order.']],
                ], 401);
            }

            if ($order->user_id !== $currentUser->id && !$currentUser->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to view this order.',
                ], 403);
            }
        } else {
            // Queried by secret order_number: if order belongs to a registered user and another user is logged in, verify ownership
            if ($order->user_id && $currentUser && $order->user_id !== $currentUser->id && !$currentUser->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to view this order.',
                ], 403);
            }
        }

        return response()->json([
            'success' => true,
            'data' => $order,
        ]);
    }

    /**
     * Admin: List all customer orders.
     */
    public function adminIndex(Request $request)
    {
        $query = Order::with(['items.dish', 'user'])->latest();

        if ($status = $request->input('status')) {
            $query->where('order_status', $status);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_phone', 'like', "%{$search}%");
            });
        }

        $orders = $query->paginate((int) $request->input('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $orders->items(),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ],
        ]);
    }

    /**
     * Admin: Update order status.
     */
    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'order_status' => 'required|in:pending,confirmed,preparing,out_for_delivery,delivered,cancelled',
            'payment_status' => 'nullable|in:pending,paid,refunded,failed',
        ]);

        $order->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Order status updated successfully',
            'data' => $order->fresh()->load('items.dish'),
        ]);
    }
}
