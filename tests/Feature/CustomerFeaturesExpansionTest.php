<?php

namespace Tests\Feature;

use App\Models\Dish;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\RestaurantTable;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CustomerFeaturesExpansionTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Dish $dish;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');

        $this->user = User::where('role', 'user')->first();
        $this->dish = Dish::first();
    }

    public function test_customer_can_view_dashboard(): void
    {
        $response = $this->actingAs($this->user)->get('/customer');
        $response->assertStatus(200);
        $response->assertSee('Welcome back');
        $response->assertSee('Recent Orders');
    }

    public function test_customer_can_update_profile_and_avatar(): void
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->image('myavatar.jpg', 200, 200);

        $response = $this->actingAs($this->user)->put('/profile', [
            'name' => 'Updated Name',
            'email' => $this->user->email,
            'phone' => '+855 99 888 777',
            'bio' => 'Food enthusiast',
            'avatar' => $file,
        ]);

        $response->assertRedirect('/profile');
        $this->user->refresh();
        $this->assertEquals('Updated Name', $this->user->name);
        $this->assertEquals('+855 99 888 777', $this->user->phone);
        $this->assertNotNull($this->user->avatar);
    }

    public function test_customer_can_manage_favorites(): void
    {
        // Clear any seeded favorites for this user
        $this->user->favorites()->delete();

        // Add to favorites
        $this->actingAs($this->user)->post("/favorites/{$this->dish->id}/toggle")->assertRedirect();
        $this->assertDatabaseHas('favorites', [
            'user_id' => $this->user->id,
            'dish_id' => $this->dish->id,
        ]);

        // View favorites page
        $response = $this->actingAs($this->user)->get('/favorites');
        $response->assertStatus(200);
        $response->assertSee($this->dish->name);

        // Toggle again to remove
        $this->actingAs($this->user)->post("/favorites/{$this->dish->id}/toggle")->assertRedirect();
        $this->assertDatabaseMissing('favorites', [
            'user_id' => $this->user->id,
            'dish_id' => $this->dish->id,
        ]);
    }

    public function test_customer_can_review_dish(): void
    {
        $this->user->reviews()->where('dish_id', $this->dish->id)->delete();

        $response = $this->actingAs($this->user)->post("/dishes/{$this->dish->id}/reviews", [
            'rating' => 5,
            'comment' => 'Exquisite Khmer flavors, loved every bite!',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('reviews', [
            'dish_id' => $this->dish->id,
            'user_id' => $this->user->id,
            'rating' => 5,
            'comment' => 'Exquisite Khmer flavors, loved every bite!',
        ]);

        $this->dish->refresh();
        $this->assertEquals(5.0, (float) $this->dish->rating);
    }

    public function test_customer_can_chat_with_ai_assistant(): void
    {
        $createRes = $this->actingAs($this->user)->post('/chat');
        $createRes->assertRedirect();

        $conversation = $this->user->chatConversations()->latest()->first();
        $this->assertNotNull($conversation);

        $sendRes = $this->actingAs($this->user)->post("/chat/{$conversation->id}/send", [
            'message' => 'What vegetarian dishes do you have?',
        ]);

        $sendRes->assertRedirect();
        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id,
            'role' => 'user',
            'content' => 'What vegetarian dishes do you have?',
        ]);

        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id,
            'role' => 'assistant',
        ]);
    }
}
