<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Conversation;

class ChatAiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_authenticated_user_can_create_chat_conversation(): void
    {
        $user = User::where('role', 'user')->first();

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/conversations', [
            'initial_message' => 'What is the best spicy Khmer dish?',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'title',
                    'messages' => [
                        '*' => ['id', 'role', 'content'],
                    ],
                ],
            ]);
    }

    public function test_user_can_send_message_to_conversation(): void
    {
        $user = User::where('role', 'user')->first();
        $conversation = Conversation::where('user_id', $user->id)->first();

        $response = $this->actingAs($user, 'sanctum')->postJson("/api/v1/conversations/{$conversation->id}/messages", [
            'message' => 'Can I get high-protein low-calorie recommendations?',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'user_message',
                    'assistant_message' => [
                        'content',
                        'metadata' => [
                            'referenced_dish_ids',
                        ],
                    ],
                ],
            ]);
    }
}
