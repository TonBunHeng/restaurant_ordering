<?php

namespace App\Services\AI;

use App\Services\AI\Providers\GeminiProvider;
use App\Services\AI\Providers\OpenAIProvider;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Support\Facades\Log;

class AIService
{
    protected TourismContextService $contextService;

    public function __construct(TourismContextService $contextService)
    {
        $this->contextService = $contextService;
    }

    /**
     * Process a chat query within a conversation, retrieve domain context, and invoke AI provider.
     *
     * @param Conversation $conversation
     * @param string $userMessage
     * @return array ['content' => string, 'metadata' => array]
     */
    public function ask(Conversation $conversation, string $userMessage): array
    {
        // 1. Retrieve RAG domain context from the database
        $contextData = $this->contextService->retrieveContext($userMessage);

        // 2. Fetch past conversation messages for context window
        $recentMessages = $conversation->messages()
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get()
            ->reverse()
            ->map(fn($m) => [
                'role' => $m->role,
                'content' => $m->content,
            ])
            ->toArray();

        // Append current message
        $recentMessages[] = [
            'role' => 'user',
            'content' => $userMessage,
        ];

        // 3. Attempt primary AI provider (Gemini)
        $aiResponseText = null;
        $providerUsed = 'local_engine';

        try {
            $geminiKey = env('AI_API_KEY');
            if (!empty($geminiKey)) {
                $gemini = new GeminiProvider($geminiKey, env('AI_MODEL', 'gemini-2.5-flash'));
                $aiResponseText = $gemini->generateResponse($recentMessages, $contextData);
                $providerUsed = 'gemini';
            }
        } catch (\Throwable $e) {
            Log::warning('Gemini AI Provider failed: ' . $e->getMessage() . '. Attempting fallback.');
        }

        // 4. Attempt backup AI provider (OpenAI) if Gemini failed or unconfigured
        if (empty($aiResponseText)) {
            try {
                $openaiKey = env('OPENAI_API_KEY');
                if (!empty($openaiKey)) {
                    $openai = new OpenAIProvider($openaiKey, env('OPENAI_MODEL', 'gpt-4o-mini'));
                    $aiResponseText = $openai->generateResponse($recentMessages, $contextData);
                    $providerUsed = 'openai';
                }
            } catch (\Throwable $e) {
                Log::warning('OpenAI AI Provider fallback failed: ' . $e->getMessage());
            }
        }

        // 5. Intelligent local tourism engine fallback if external APIs are unconfigured or offline
        if (empty($aiResponseText)) {
            $aiResponseText = $this->generateLocalTourismFallback($userMessage, $contextData);
            $providerUsed = 'cambodia_rag_engine';
        }

        $metadata = [
            'provider' => $providerUsed,
            'referenced_place_ids' => $contextData['referenced_place_ids'] ?? [],
            'detected_provinces' => $contextData['detected_provinces'] ?? [],
        ];

        return [
            'content' => $aiResponseText,
            'metadata' => $metadata,
        ];
    }

    /**
     * Fallback domain engine providing high-quality verified responses from database data.
     */
    protected function generateLocalTourismFallback(string $userMessage, array $contextData): string
    {
        $response = "Hello! Here is verified travel information based on Cambodia's official tourism database:\n\n";

        if (!empty($contextData['places_summary'])) {
            $response .= "### 🏛️ Verified Destinations & Attractions\n";
            $response .= $contextData['places_summary'] . "\n";
        }

        if (!empty($contextData['events_summary'])) {
            $response .= "### 🎪 Upcoming Cultural Events & Festivals\n";
            $response .= $contextData['events_summary'] . "\n";
        }

        $response .= "### 💡 Essential Travel & Cultural Tips for Cambodia\n" .
                     "* **Temple Dress Code**: Shoulders and knees must be covered when visiting sacred sites like Angkor Wat and the Royal Palace.\n" .
                     "* **Currency**: US Dollars and Cambodian Riel (KHR) are widely accepted (approx. 4,000 KHR = $1 USD).\n" .
                     "* **Best Season to Visit**: The dry season from November to April offers comfortable temperatures and sunny days.\n" .
                     "* **Transportation**: Use local ride-hailing apps like Grab or PassApp for reliable, metered tuk-tuk rides in Phnom Penh and Siem Reap.";

        return $response;
    }
}
