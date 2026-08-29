<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Dish;
use App\Models\Message;
use App\Models\RestaurantSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ChatController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $conversations = $user->conversations()->with('messages')->latest()->get();

        $currentConversation = null;
        if ($request->filled('conversation_id')) {
            $currentConversation = $conversations->firstWhere('id', $request->conversation_id);
        }

        if (!$currentConversation && $conversations->isNotEmpty()) {
            $currentConversation = $conversations->first();
        }

        return view('chat.index', compact('conversations', 'currentConversation', 'user'));
    }

    public function createConversation(Request $request)
    {
        $user = auth()->user();
        $conversation = Conversation::create([
            'user_id' => $user->id,
            'title' => 'Menu & Dining Advice',
        ]);

        return redirect()->route('chat.index', ['conversation_id' => $conversation->id]);
    }

    public function sendMessage(Request $request, Conversation $conversation)
    {
        $user = auth()->user();

        if ($conversation->user_id !== $user->id && !$user->isAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $userMsg = Message::create([
            'conversation_id' => $conversation->id,
            'role' => 'user',
            'content' => $validated['message'],
        ]);

        $aiResponse = $this->generateAiReply($validated['message'], $user);

        $assistantMsg = Message::create([
            'conversation_id' => $conversation->id,
            'role' => 'assistant',
            'content' => $aiResponse,
        ]);

        if ($conversation->messages()->count() <= 2) {
            $conversation->update(['title' => Str::limit($validated['message'], 30)]);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'user_message' => $userMsg,
                'assistant_message' => $assistantMsg,
            ]);
        }

        return redirect()->route('chat.index', ['conversation_id' => $conversation->id]);
    }

    protected function generateAiReply(string $prompt, $user): string
    {
        $lower = strtolower($prompt);
        $restaurantName = RestaurantSetting::get('name', 'Royal Khmer Kitchen');
        $openingTime = RestaurantSetting::get('opening_time', '10:00');
        $closingTime = RestaurantSetting::get('closing_time', '22:00');

        if (str_contains($lower, 'order status') || str_contains($lower, 'my order')) {
            $lastOrder = $user->orders()->latest()->first();
            if ($lastOrder) {
                return "Your most recent order is **#{$lastOrder->order_number}** ({$lastOrder->formatted_order_type}). Current status: **" . ucfirst($lastOrder->order_status) . "**, total amount: **$" . number_format($lastOrder->total_amount, 2) . "**.";
            }
            return "You do not have any active orders currently. You can browse our delicious menu anytime!";
        }

        if (str_contains($lower, 'reservation') || str_contains($lower, 'booking')) {
            $lastRes = $user->reservations()->latest('reservation_date')->first();
            if ($lastRes) {
                return "Your upcoming reservation is **#{$lastRes->reservation_number}** on **{$lastRes->reservation_date->format('M d, Y')}** at **{$lastRes->reservation_time}** for **{$lastRes->guest_count} guests** (Status: **" . ucfirst($lastRes->status) . "**).";
            }
            return "We are open for table reservations daily from {$openingTime} to {$closingTime}. You can book a table easily from the 'Book a Table' page!";
        }

        if (str_contains($lower, 'vegetarian') || str_contains($lower, 'vegan')) {
            $dishes = Dish::where('is_vegetarian', true)->where('status', 'published')->where('is_available', true)->take(3)->get();
            $reply = "🌿 **Vegetarian Specialties at {$restaurantName}:**\n\n";
            foreach ($dishes as $d) {
                $price = number_format($d->discount_price ?: $d->price, 2);
                $reply .= "• **{$d->name}** (\${$price}) - {$d->short_description}\n";
            }
            return $reply . "\nAll our vegetable dishes are prepared with 100% fresh organic local produce.";
        }

        if (str_contains($lower, 'halal')) {
            $dishes = Dish::where('is_halal', true)->where('status', 'published')->where('is_available', true)->take(3)->get();
            if ($dishes->isNotEmpty()) {
                $reply = "🕌 **Halal Certified Dishes:**\n\n";
                foreach ($dishes as $d) {
                    $price = number_format($d->discount_price ?: $d->price, 2);
                    $reply .= "• **{$d->name}** (\${$price})\n";
                }
                return $reply;
            }
            return "Our kitchen uses certified halal poultry and beef for selected signature curry and grilled items. Feel free to specify dietary requests in your order notes!";
        }

        if (str_contains($lower, 'spicy') || str_contains($lower, 'chili')) {
            $dishes = Dish::where('is_spicy', true)->where('status', 'published')->where('is_available', true)->take(3)->get();
            $reply = "🌶️ **Spicy Favorites:**\n\n";
            foreach ($dishes as $d) {
                $price = number_format($d->discount_price ?: $d->price, 2);
                $reply .= "• **{$d->name}** (\${$price}) - {$d->short_description}\n";
            }
            return $reply . "\nYou can always customize your preferred spice level when ordering!";
        }

        if (str_contains($lower, 'special') || str_contains($lower, 'recommend') || str_contains($lower, 'popular')) {
            $dishes = Dish::where('is_chef_special', true)->where('status', 'published')->where('is_available', true)->take(3)->get();
            $reply = "⭐ **Chef's Specials & House Favorites:**\n\n";
            foreach ($dishes as $d) {
                $price = number_format($d->discount_price ?: $d->price, 2);
                $reply .= "• **{$d->name}** (\${$price}) - ★ {$d->average_rating} ({$d->reviews_count} reviews)\n";
            }
            return $reply;
        }

        return "Welcome to {$restaurantName}! We serve fresh, authentic cuisine prepared daily. Our opening hours are {$openingTime} to {$closingTime}. Ask me about our specials, vegetarian dishes, spicy meals, or your order status!";
    }
}
