<?php

namespace App\Http\Controllers;

use App\Models\Dish;
use App\Models\Promotion;
use App\Models\RestaurantSetting;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index()
    {
        $cart = session()->get('cart', []);
        $cartItems = [];
        $subtotal = 0;

        if (!empty($cart)) {
            $dishIds = array_keys($cart);
            $dishes = Dish::whereIn('id', $dishIds)->where('status', 'published')->get()->keyBy('id');

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
        }

        // Settings
        $taxPercent = (float) RestaurantSetting::get('tax_percentage', 0);
        $servicePercent = (float) RestaurantSetting::get('service_charge_percentage', 0);
        $threshold = (float) RestaurantSetting::get('free_delivery_threshold', 30.00);
        $standardFee = (float) RestaurantSetting::get('delivery_fee', 2.00);
        $deliveryFee = ($subtotal > 0 && $subtotal < $threshold) ? $standardFee : 0.00;

        // Promo code check
        $promoCode = session()->get('applied_promo');
        $discountAmount = 0.00;
        $promo = null;

        if ($promoCode && $subtotal > 0) {
            $promo = Promotion::where('code', $promoCode)->first();
            $error = null;
            if ($promo && $promo->isValidForAmount($subtotal, $error)) {
                $discountAmount = $promo->calculateDiscount($subtotal);
            } else {
                session()->forget('applied_promo');
                $promo = null;
            }
        }

        $taxAmount = round(($subtotal - $discountAmount) * ($taxPercent / 100), 2);
        $serviceAmount = round(($subtotal - $discountAmount) * ($servicePercent / 100), 2);
        $total = max(0, ($subtotal - $discountAmount) + $taxAmount + $serviceAmount + $deliveryFee);

        return view('cart.index', compact(
            'cartItems',
            'subtotal',
            'discountAmount',
            'taxPercent',
            'taxAmount',
            'servicePercent',
            'serviceAmount',
            'deliveryFee',
            'total',
            'threshold',
            'promo'
        ));
    }

    public function add(Request $request)
    {
        $request->validate([
            'dish_id' => 'required|exists:dishes,id',
            'quantity' => 'nullable|integer|min:1|max:50',
            'special_instructions' => 'nullable|string|max:255',
        ]);

        $dish = Dish::findOrFail($request->dish_id);

        if (!$dish->is_available || $dish->status !== 'published') {
            return back()->with('error', "Sorry, '{$dish->name}' is currently unavailable.");
        }

        $quantity = (int) $request->input('quantity', 1);
        $cart = session()->get('cart', []);

        if (isset($cart[$dish->id])) {
            $cart[$dish->id]['quantity'] += $quantity;
            if ($request->filled('special_instructions')) {
                $cart[$dish->id]['special_instructions'] = $request->special_instructions;
            }
        } else {
            $cart[$dish->id] = [
                'quantity' => $quantity,
                'special_instructions' => $request->special_instructions ?? null,
            ];
        }

        session()->put('cart', $cart);

        return redirect()->route('cart.index')->with('success', "Added '{$dish->name}' to your cart.");
    }

    public function update(Request $request)
    {
        $request->validate([
            'dish_id' => 'required|exists:dishes,id',
            'quantity' => 'required|integer|min:1|max:50',
        ]);

        $cart = session()->get('cart', []);

        if (isset($cart[$request->dish_id])) {
            $cart[$request->dish_id]['quantity'] = (int) $request->quantity;
            session()->put('cart', $cart);
            return back()->with('success', 'Cart updated successfully.');
        }

        return back()->with('error', 'Item not found in cart.');
    }

    public function remove(Request $request)
    {
        $request->validate([
            'dish_id' => 'required|integer',
        ]);

        $cart = session()->get('cart', []);

        if (isset($cart[$request->dish_id])) {
            unset($cart[$request->dish_id]);
            session()->put('cart', $cart);
            return back()->with('success', 'Item removed from your cart.');
        }

        return back()->with('error', 'Item not found in cart.');
    }

    public function applyPromo(Request $request)
    {
        $request->validate([
            'promo_code' => 'required|string|max:50',
        ]);

        $cart = session()->get('cart', []);
        if (empty($cart)) {
            return back()->with('error', 'Please add items to cart before applying a promo code.');
        }

        $dishIds = array_keys($cart);
        $dishes = Dish::whereIn('id', $dishIds)->where('status', 'published')->get()->keyBy('id');
        $subtotal = 0;
        foreach ($cart as $id => $item) {
            if (isset($dishes[$id])) {
                $subtotal += ($dishes[$id]->discount_price ?: $dishes[$id]->price) * $item['quantity'];
            }
        }

        $code = strtoupper(trim($request->promo_code));
        $promo = Promotion::where('code', $code)->first();

        if (!$promo) {
            return back()->with('error', 'Invalid promo code.');
        }

        $errorMsg = null;
        if (!$promo->isValidForAmount($subtotal, $errorMsg)) {
            return back()->with('error', $errorMsg ?: 'Promo code cannot be applied.');
        }

        session()->put('applied_promo', $promo->code);

        return back()->with('success', "Promo code '{$promo->code}' applied successfully!");
    }

    public function removePromo()
    {
        session()->forget('applied_promo');
        return back()->with('success', 'Promo code removed.');
    }

    public function clear()
    {
        session()->forget('cart');
        session()->forget('applied_promo');
        return redirect()->route('cart.index')->with('success', 'Cart cleared.');
    }
}
