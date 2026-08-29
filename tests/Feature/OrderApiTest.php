<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Dish;
use App\Models\Order;

class OrderApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_can_place_order_with_items(): void
    {
        $user = User::where('role', 'user')->first();
        $dishes = Dish::take(2)->get();

        $payload = [
            'customer_name' => 'Alex Rivera',
            'customer_phone' => '+855 12 999 888',
            'customer_email' => 'alex@example.com',
            'delivery_address' => 'Street 240, Phnom Penh',
            'payment_method' => 'cash_on_delivery',
            'items' => [
                [
                    'dish_id' => $dishes[0]->id,
                    'quantity' => 2,
                    'special_instructions' => 'Mild spice',
                ],
                [
                    'dish_id' => $dishes[1]->id,
                    'quantity' => 1,
                ],
            ],
        ];

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/orders', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'customer_name' => 'Alex Rivera',
                    'order_status' => 'pending',
                ],
            ]);

        $this->assertDatabaseHas('orders', [
            'customer_name' => 'Alex Rivera',
        ]);
    }

    public function test_admin_can_update_order_status(): void
    {
        $admin = User::where('role', 'super_admin')->first();
        $order = Order::first();

        $response = $this->actingAs($admin, 'sanctum')->putJson("/api/v1/admin/orders/{$order->id}/status", [
            'order_status' => 'out_for_delivery',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'order_status' => 'out_for_delivery',
                ],
            ]);
    }

    public function test_cannot_order_unavailable_dish(): void
    {
        $user = User::where('role', 'user')->first();
        $unavailableDish = Dish::first();
        $unavailableDish->update(['is_available' => false]);

        $payload = [
            'customer_name' => 'Alex Rivera',
            'customer_phone' => '+855 12 999 888',
            'delivery_address' => 'Street 240, Phnom Penh',
            'payment_method' => 'cash_on_delivery',
            'items' => [
                [
                    'dish_id' => $unavailableDish->id,
                    'quantity' => 1,
                ],
            ],
        ];

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/orders', $payload);

        $response->assertStatus(422)
            ->assertJsonStructure(['errors' => ['items']]);
    }

    public function test_user_cannot_view_another_users_order_by_id(): void
    {
        $owner = User::where('role', 'user')->first();
        $otherUser = User::create([
            'name' => 'Stranger',
            'email' => 'stranger@example.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password123'),
            'role' => 'user',
            'status' => 'active',
        ]);

        $order = Order::create([
            'user_id' => $owner->id,
            'subtotal' => 20.00,
            'delivery_fee' => 2.00,
            'total_amount' => 22.00,
            'payment_method' => 'cash_on_delivery',
            'payment_status' => 'pending',
            'order_status' => 'pending',
            'customer_name' => 'Owner',
            'customer_phone' => '+855 12 345 678',
            'delivery_address' => 'Phnom Penh',
        ]);

        // Accessing with numeric ID as another user must return 403 Forbidden
        $response = $this->actingAs($otherUser, 'sanctum')->getJson("/api/v1/orders/{$order->id}");
        $response->assertStatus(403);

        // Accessing as the owner must succeed
        $ownerResponse = $this->actingAs($owner, 'sanctum')->getJson("/api/v1/orders/{$order->id}");
        $ownerResponse->assertStatus(200);
    }
}
