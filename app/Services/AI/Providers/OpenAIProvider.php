<?php

namespace App\Services\AI\Providers;

use App\Services\AI\AIProviderInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAIProvider implements AIProviderInterface
{
    protected ?string $apiKey;
    protected string $model;

    public function __construct(?string $apiKey = null, ?string $model = null)
    {
        $this->apiKey = $apiKey ?: config('services.openai.key', env('OPENAI_API_KEY'));
        $this->model = $model ?: config('services.openai.model', env('OPENAI_MODEL', 'gpt-4o-mini'));
    }

    public function generateResponse(array $messages, array $contextData = []): string
    {
        if (empty($this->apiKey)) {
            throw new \Exception('OpenAI API key is not configured.');
        }

        $systemPrompt = "You are the specialized AI Tourism Assistant for Cambodia.\n" .
                        "Provide accurate travel advice, itineraries, ticket costs, and cultural tips using the verified database context below.\n\n";

        if (!empty($contextData['places_summary'])) {
            $systemPrompt .= "=== VERIFIED TOURIST PLACES ===\n" . $contextData['places_summary'] . "\n\n";
        }

        if (!empty($contextData['events_summary'])) {
            $systemPrompt .= "=== UPCOMING EVENTS ===\n" . $contextData['events_summary'] . "\n\n";
        }

        $formattedMessages = [
            ['role' => 'system', 'content' => $systemPrompt],
        ];

        foreach ($messages as $msg) {
            $formattedMessages[] = [
                'role' => $msg['role'],
                'content' => $msg['content'],
            ];
        }

        $response = Http::withToken($this->apiKey)
            ->timeout(30)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => $this->model,
                'messages' => $formattedMessages,
                'temperature' => 0.7,
                'max_tokens' => 2048,
            ]);

        if (!$response->successful()) {
            $errorBody = $response->body();
            Log::error('OpenAI API Error: ' . $errorBody);
            throw new \Exception('OpenAI API request failed: ' . ($response->json('error.message') ?? $errorBody));
        }

        $text = $response->json('choices.0.message.content');
        if (empty($text)) {
            throw new \Exception('OpenAI returned an empty response.');
        }

        return $text;
    }
}
