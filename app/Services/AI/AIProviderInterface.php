<?php

namespace App\Services\AI;

interface AIProviderInterface
{
    /**
     * Generate an AI response given message history and retrieved tourism context.
     *
     * @param array $messages Array of ['role' => 'user'|'assistant'|'system', 'content' => string]
     * @param array $contextData Structured domain data retrieved from the database
     * @return string
     * @throws \Exception
     */
    public function generateResponse(array $messages, array $contextData = []): string;
}
