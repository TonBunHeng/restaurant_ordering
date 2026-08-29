<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Dish;

class ReviewAndFavoriteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_user_can_submit_dish_review(): void
    {
        $user = User::where('role', 'user')->first();
        $dish = Dish::whereDoesntHave('reviews', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->first();

        $payload = [
            'rating' => 5,
            'title' => 'Exceptional taste!',
            'comment' => 'Fresh herbs and perfectly seasoned.',
        ];

        $response = $this->actingAs($user, 'sanctum')->postJson("/api/v1/dishes/{$dish->id}/reviews", $payload);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'rating' => 5,
                ],
            ]);
    }

    public function test_user_can_toggle_favorite_dish(): void
    {
        $user = User::where('role', 'user')->first();
        $dish = Dish::first();

        $response = $this->actingAs($user, 'sanctum')->postJson("/api/v1/favorites/toggle/{$dish->id}");
        $response->assertStatus(200);

        $this->assertArrayHasKey('is_favorited', $response->json('data'));
    }
}
