<?php

namespace App\Services\AI\Providers;

use App\Services\AI\AIProviderInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiProvider implements AIProviderInterface
{
    protected ?string $apiKey;
    protected string $model;

    public function __construct(?string $apiKey = null, ?string $model = null)
    {
        $this->apiKey = $apiKey ?: config('services.gemini.key', env('AI_API_KEY'));
        $this->model = $model ?: config('services.gemini.model', env('AI_MODEL', 'gemini-2.5-flash'));
    }

    public function generateResponse(array $messages, array $contextData = []): string
    {
        if (empty($this->apiKey)) {
            throw new \Exception('Gemini API key is not configured.');
        }

        // Format contents for Gemini API
        $contents = [];
        $systemInstruction = $this->buildSystemInstruction($contextData);

        foreach ($messages as $msg) {
            $role = $msg['role'] === 'assistant' ? 'model' : 'user';
            
            // Skip system messages in contents array; handled in systemInstruction
            if ($msg['role'] === 'system') {
                continue;
            }

            $contents[] = [
                'role' => $role,
                'parts' => [
                    ['text' => $msg['content']],
                ],
            ];
        }

        $payload = [
            'contents' => $contents,
            'generationConfig' => [
                'temperature' => 0.7,
                'topK' => 40,
                'topP' => 0.95,
                'maxOutputTokens' => 2048,
            ],
        ];

        if (!empty($systemInstruction)) {
            $payload['systemInstruction'] = [
                'parts' => [
                    ['text' => $systemInstruction],
                ],
            ];
        }

        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$this->apiKey}";

        $response = Http::timeout(30)->post($url, $payload);

        if (!$response->successful()) {
            $errorBody = $response->body();
            Log::error('Gemini API Error: ' . $errorBody);
            throw new \Exception('Gemini API request failed: ' . ($response->json('error.message') ?? $errorBody));
        }

        $candidates = $response->json('candidates');
        if (empty($candidates) || empty($candidates[0]['content']['parts'][0]['text'])) {
            throw new \Exception('Gemini API returned an empty response.');
        }

        return $candidates[0]['content']['parts'][0]['text'];
    }

    protected function buildSystemInstruction(array $contextData): string
    {
        $prompt = "You are the AI Tourism Assistant for the Kingdom of Cambodia (Wonder of the World).\n" .
                  "Your mission is to provide accurate, helpful, friendly, and practical travel guidance for exploring Cambodia.\n" .
                  "Key Guidelines:\n" .
                  "1. Prioritize authentic Cambodian knowledge across Phnom Penh, Siem Reap, Angkor Wat, Battambang, Kampot, Kep, Koh Rong, Preah Vihear, Mondulkiri, etc.\n" .
                  "2. Use the provided real-world database context for accurate prices, opening hours, locations, and descriptions.\n" .
                  "3. When facts or specific details are not available, clearly indicate uncertainty rather than inventing information.\n" .
                  "4. Offer cultural etiquette tips (e.g., dress codes for temples with covered shoulders and knees, respectful greetings with the Sampeah).\n" .
                  "5. Format responses neatly with markdown headings, bullet points, and highlight entrance fees and best visiting times.\n\n";

        if (!empty($contextData['places_summary'])) {
            $prompt .= "=== VERIFIED DATABASE CONTEXT (TOURIST PLACES & ATTRACTIONS) ===\n" . $contextData['places_summary'] . "\n\n";
        }

        if (!empty($contextData['events_summary'])) {
            $prompt .= "=== UPCOMING CULTURAL EVENTS & FESTIVALS ===\n" . $contextData['events_summary'] . "\n\n";
        }

        return $prompt;
    }
}
