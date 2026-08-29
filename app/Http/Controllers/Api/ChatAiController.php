<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Dish;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ChatAiController extends Controller
{
    /**
     * Get conversations of the authenticated user.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $conversations = $user->conversations()->latest()->get();

        return response()->json([
            'success' => true,
            'data' => $conversations,
        ]);
    }

    /**
     * Start a new food recommendation conversation.
     */
    public function store(Request $request)
    {
        $user = $request->user();
        $initialMessage = $request->input('initial_message');

        $title = 'Food & Menu Advice';
        if ($initialMessage) {
            $title = Str::limit($initialMessage, 35, '...');
        }

        $conversation = Conversation::create([
            'user_id' => $user->id,
            'title' => $title,
        ]);

        if ($initialMessage) {
            $userMsg = Message::create([
                'conversation_id' => $conversation->id,
                'role' => 'user',
                'content' => $initialMessage,
            ]);

            $aiResponse = $this->generateFoodAssistantResponse($initialMessage, $conversation);

            $assistantMsg = Message::create([
                'conversation_id' => $conversation->id,
                'role' => 'assistant',
                'content' => $aiResponse['content'],
                'metadata' => $aiResponse['metadata'],
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $conversation->load('messages'),
        ], 201);
    }

    /**
     * Show a conversation with messages.
     */
    public function show(Request $request, Conversation $conversation)
    {
        $user = $request->user();

        if ($conversation->user_id !== $user->id && !$user->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $conversation->load('messages'),
        ]);
    }

    /**
     * Send a message to the AI Menu & Nutrition Assistant.
     */
    public function sendMessage(Request $request, Conversation $conversation)
    {
        $user = $request->user();

        if ($conversation->user_id !== $user->id && !$user->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        $userMsg = Message::create([
            'conversation_id' => $conversation->id,
            'role' => 'user',
            'content' => $validated['message'],
        ]);

        $aiResponse = $this->generateFoodAssistantResponse($validated['message'], $conversation);

        $assistantMsg = Message::create([
            'conversation_id' => $conversation->id,
            'role' => 'assistant',
            'content' => $aiResponse['content'],
            'metadata' => $aiResponse['metadata'],
        ]);

        if ($conversation->messages()->count() <= 2) {
            $conversation->update([
                'title' => Str::limit($validated['message'], 35, '...'),
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'user_message' => $userMsg,
                'assistant_message' => $assistantMsg,
            ],
        ]);
    }

    /**
     * Delete a conversation.
     */
    public function destroy(Request $request, Conversation $conversation)
    {
        $user = $request->user();

        if ($conversation->user_id !== $user->id && !$user->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $conversation->delete();

        return response()->json([
            'success' => true,
            'message' => 'Conversation deleted',
        ]);
    }

    /**
     * Intelligent RAG Domain Logic grounded in Restaurant Dishes.
     */
    protected function generateFoodAssistantResponse(string $prompt, Conversation $conversation): array
    {
        $lowerPrompt = strtolower($prompt);
        $referencedDishIds = [];

        // Retrieve relevant dishes based on keywords
        $allDishes = Dish::with('category')->published()->get();
        $matchedDishes = collect();

        foreach ($allDishes as $dish) {
            $dishName = strtolower($dish->name);
            $catName = strtolower($dish->category->name ?? '');

            if (
                str_contains($lowerPrompt, $dishName) ||
                (str_contains($lowerPrompt, 'amok') && str_contains($dishName, 'amok')) ||
                (str_contains($lowerPrompt, 'lok lak') && str_contains($dishName, 'lok lak')) ||
                (str_contains($lowerPrompt, 'steak') && str_contains($dishName, 'steak')) ||
                (str_contains($lowerPrompt, 'wagyu') && str_contains($dishName, 'wagyu')) ||
                (str_contains($lowerPrompt, 'burger') && str_contains($dishName, 'burger')) ||
                (str_contains($lowerPrompt, 'pizza') && str_contains($dishName, 'pizza')) ||
                (str_contains($lowerPrompt, 'noodle') && str_contains($dishName, 'kuy teav')) ||
                (str_contains($lowerPrompt, 'tuna') && str_contains($dishName, 'tuna')) ||
                (str_contains($lowerPrompt, 'dessert') && str_contains($catName, 'dessert')) ||
                (str_contains($lowerPrompt, 'mango') && str_contains($dishName, 'mango')) ||
                (str_contains($lowerPrompt, 'coffee') && str_contains($dishName, 'latte')) ||
                (str_contains($lowerPrompt, 'spicy') && $dish->is_spicy) ||
                (str_contains($lowerPrompt, 'vegetarian') && $dish->is_vegetarian) ||
                (str_contains($lowerPrompt, 'healthy') && str_contains($catName, 'salad'))
            ) {
                $matchedDishes->push($dish);
            }
        }

        if ($matchedDishes->isEmpty()) {
            $matchedDishes = Dish::with('category')->chefSpecials()->take(3)->get();
        } else {
            $matchedDishes = $matchedDishes->unique('id')->take(4);
        }

        $referencedDishIds = $matchedDishes->pluck('id')->toArray();

        // Construct customized advice
        if (str_contains($lowerPrompt, 'vegetarian') || str_contains($lowerPrompt, 'vegan')) {
            $content = "🌿 **Vegetarian & Plant-Forward Recommendations**\n\nHere are delicious meat-free options prepared fresh by our culinary team:\n\n";
            foreach ($matchedDishes as $d) {
                $priceStr = '$' . number_format($d->price, 2);
                $content .= "• **{$d->name}** ({$priceStr}) — {$d->calories} kcal\n  {$d->short_description}\n\n";
            }
            $content .= "Our kitchen can also customize our noodle broths and stir-fries with organic tofu and mushrooms on request!";
        } elseif (str_contains($lowerPrompt, 'calorie') || str_contains($lowerPrompt, 'diet') || str_contains($lowerPrompt, 'healthy')) {
            $content = "🥗 **Nutritional Guidance & Light Options**\n\nFor a balanced, protein-rich meal with clean calories:\n\n";
            foreach ($matchedDishes as $d) {
                $priceStr = '$' . number_format($d->price, 2);
                $content .= "• **{$d->name}** ({$priceStr}) — **{$d->calories} Calories**\n  Prep time: {$d->preparation_time} mins. {$d->short_description}\n\n";
            }
            $content .= "Feel free to ask for dressings on the side or extra grilled proteins!";
        } elseif (str_contains($lowerPrompt, 'spicy') || str_contains($lowerPrompt, 'chili')) {
            $content = "🌶️ **Spicy & Bold Flavors**\n\nIf you enjoy authentic spice and zesty kick:\n\n";
            foreach ($matchedDishes as $d) {
                $priceStr = '$' . number_format($d->price, 2);
                $content .= "• **{$d->name}** ({$priceStr})\n  {$d->description}\n\n";
            }
            $content .= "You can specify your preferred spice level (Mild, Medium, Khmer Hot) in your order instructions!";
        } else {
            $content = "🍽️ **Chef's Curated Recommendations**\n\nBased on our most popular and highest-rated culinary dishes:\n\n";
            foreach ($matchedDishes as $d) {
                $priceStr = '$' . number_format($d->discount_price ?: $d->price, 2);
                $content .= "• **{$d->name}** ({$priceStr})\n  ★ {$d->average_rating} ({$d->reviews_count} reviews) • {$d->preparation_time} min prep\n  {$d->short_description}\n\n";
            }
            $content .= "Would you like to add any of these items to your cart for delivery, or reserve a table for dine-in?";
        }

        return [
            'content' => $content,
            'metadata' => [
                'referenced_dish_ids' => $referencedDishIds,
                'engine' => 'FastBite-Domain-RAG-v2',
            ],
        ];
    }
}
