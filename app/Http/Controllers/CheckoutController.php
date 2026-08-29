<?php

namespace App\Http\Controllers;

use App\Models\Dish;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Promotion;
use App\Models\RestaurantSetting;
use App\Models\RestaurantTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function index()
    {
        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return redirect()->route('menu.index')->with('info', 'Your cart is empty. Please select food from our menu to checkout.');
        }

        $dishIds = array_keys($cart);
        $dishes = Dish::whereIn('id', $dishIds)->where('status', 'published')->get()->keyBy('id');

        $cartItems = [];
        $subtotal = 0;

        foreach ($cart as $id => $details) {
            if (isset($dishes[$id])) {
                $dish = $dishes[$id];
                $price = $dish->discount_price ?: $dish->price;
                $lineTotal = $price * $details['quantity'];
                $subtotal += $lineTotal;

                $cartItems[] = [
                    'dish' => $dish,
                    'quantity' => $details['quantity'],
                    'price' => $price,
                    'line_total' => $lineTotal,
                    'special_instructions' => $details['special_instructions'] ?? null,
                ];
            }
        }

        if (empty($cartItems)) {
            session()->forget('cart');
            return redirect()->route('menu.index')->with('error', 'Selected items are no longer available.');
        }

        // Settings & Fees
        $taxPercent = (float) RestaurantSetting::get('tax_percentage', 0);
        $servicePercent = (float) RestaurantSetting::get('service_charge_percentage', 0);
        $threshold = (float) RestaurantSetting::get('free_delivery_threshold', 30.00);
        $standardFee = (float) RestaurantSetting::get('delivery_fee', 2.00);
        $deliveryFee = ($subtotal > 0 && $subtotal < $threshold) ? $standardFee : 0.00;

        // Promo
        $promoCode = session()->get('applied_promo');
        $discountAmount = 0.00;
        $promo = null;
        if ($promoCode) {
            $promo = Promotion::where('code', $promoCode)->first();
            $err = null;
            if ($promo && $promo->isValidForAmount($subtotal, $err)) {
                $discountAmount = $promo->calculateDiscount($subtotal);
            }
        }

        $taxAmount = round(($subtotal - $discountAmount) * ($taxPercent / 100), 2);
        $serviceAmount = round(($subtotal - $discountAmount) * ($servicePercent / 100), 2);
        $total = max(0, ($subtotal - $discountAmount) + $taxAmount + $serviceAmount + $deliveryFee);

        $tables = RestaurantTable::where('status', '!=', 'unavailable')->orderBy('table_number', 'asc')->get();
        $user = auth()->user();

        return view('checkout.index', compact(
            'cartItems',
            'subtotal',
            'discountAmount',
            'taxPercent',
            'taxAmount',
            'servicePercent',
            'serviceAmount',
            'deliveryFee',
            'total',
            'user',
            'threshold',
            'promo',
            'tables'
        ));
    }

    public function process(Request $request)
    {
        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return redirect()->route('menu.index')->with('error', 'Your cart is empty.');
        }

        $validated = $request->validate([
            'order_type' => 'nullable|in:dine_in,takeaway,delivery',
            'table_number' => 'nullable|required_if:order_type,dine_in|string|max:50',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:50',
            'customer_email' => 'nullable|email',
            'delivery_address' => 'nullable|string|max:500',
            'payment_method' => 'required|in:cash_on_delivery,aba_pay,credit_card,qr_payment',
            'notes' => 'nullable|string|max:500',
        ]);

        $validated['order_type'] = $validated['order_type'] ?? 'delivery';

        try {
            $order = DB::transaction(function () use ($validated, $cart, $request) {
                $subtotal = 0;
                $orderItemsData = [];
                $maxPrepTime = 20;

                foreach ($cart as $dishId => $details) {
                    $dish = Dish::findOrFail($dishId);

                    if (!$dish->is_available || $dish->status !== 'published') {
                        throw new \Exception("The dish '{$dish->name}' is currently unavailable.");
                    }

                    $unitPrice = $dish->discount_price ?: $dish->price;
                    $quantity = (int) $details['quantity'];
                    $lineTotal = $unitPrice * $quantity;
                    $subtotal += $lineTotal;

                    if ($dish->preparation_time > $maxPrepTime) {
                        $maxPrepTime = $dish->preparation_time;
                    }

                    $orderItemsData[] = [
                        'dish_id' => $dish->id,
                        'dish_name' => $dish->name,
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'subtotal_price' => $lineTotal,
                        'special_instructions' => $details['special_instructions'] ?? null,
                    ];
                }

                // Calculate Discounts
                $promoCode = session()->get('applied_promo');
                $discountAmount = 0.00;
                $appliedPromo = null;

                if ($promoCode) {
                    $appliedPromo = Promotion::where('code', $promoCode)->lockForUpdate()->first();
                    $err = null;
                    if ($appliedPromo && $appliedPromo->isValidForAmount($subtotal, $err)) {
                        $discountAmount = $appliedPromo->calculateDiscount($subtotal);
                        $appliedPromo->increment('times_used');
                    }
                }

                // Settings
                $taxPercent = (float) RestaurantSetting::get('tax_percentage', 0);
                $servicePercent = (float) RestaurantSetting::get('service_charge_percentage', 0);
                $threshold = (float) RestaurantSetting::get('free_delivery_threshold', 30.00);
                $standardFee = (float) RestaurantSetting::get('delivery_fee', 2.00);

                // Delivery fee applies only for delivery order type
                $deliveryFee = 0.00;
                if ($validated['order_type'] === 'delivery') {
                    $deliveryFee = ($subtotal > 0 && $subtotal < $threshold) ? $standardFee : 0.00;
                }

                $taxAmount = round(($subtotal - $discountAmount) * ($taxPercent / 100), 2);
                $serviceAmount = round(($subtotal - $discountAmount) * ($servicePercent / 100), 2);
                $totalAmount = max(0, ($subtotal - $discountAmount) + $taxAmount + $serviceAmount + $deliveryFee);

                $deliveryAddress = $validated['delivery_address'] ?? 'Dine-in / Takeaway Pickup';
                if ($validated['order_type'] === 'dine_in') {
                    $deliveryAddress = 'Table ' . ($validated['table_number'] ?? 'Dine-in');
                } elseif ($validated['order_type'] === 'takeaway') {
                    $deliveryAddress = 'Takeaway Pickup';
                }

                $order = Order::create([
                    'user_id' => auth()->id(),
                    'order_type' => $validated['order_type'],
                    'table_number' => $validated['table_number'] ?? null,
                    'subtotal' => $subtotal,
                    'delivery_fee' => $deliveryFee,
                    'discount_amount' => $discountAmount,
                    'tax_amount' => $taxAmount,
                    'service_charge' => $serviceAmount,
                    'promo_code' => $appliedPromo ? $appliedPromo->code : null,
                    'total_amount' => $totalAmount,
                    'payment_method' => $validated['payment_method'],
                    'payment_status' => $validated['payment_method'] === 'cash_on_delivery' ? 'pending' : 'paid',
                    'order_status' => 'pending',
                    'customer_name' => $validated['customer_name'],
                    'customer_phone' => $validated['customer_phone'],
                    'customer_email' => $validated['customer_email'] ?? auth()->user()?->email,
                    'delivery_address' => $deliveryAddress,
                    'notes' => $validated['notes'] ?? null,
                    'estimated_prep_time' => $maxPrepTime,
                ]);

                foreach ($orderItemsData as $itemData) {
                    $order->items()->create($itemData);
                }

                // Create Payment record
                Payment::create([
                    'order_id' => $order->id,
                    'payment_method' => $validated['payment_method'],
                    'amount' => $totalAmount,
                    'status' => $validated['payment_method'] === 'cash_on_delivery' ? 'pending' : 'paid',
                    'transaction_reference' => 'TXN-' . strtoupper(uniqid()),
                    'paid_at' => $validated['payment_method'] === 'cash_on_delivery' ? null : now(),
                ]);

                return $order;
            });

            session()->forget('cart');
            session()->forget('applied_promo');

            return redirect()->route('orders.show', $order->order_number)
                ->with('success', 'Your order has been placed successfully! The kitchen is preparing your meal.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }
}
