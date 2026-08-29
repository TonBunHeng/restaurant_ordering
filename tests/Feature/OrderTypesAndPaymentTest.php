<?php

namespace Tests\Feature;

use App\Models\Dish;
use App\Models\Order;
use App\Models\Payment;
use App\Models\RestaurantTable;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTypesAndPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Dish $dish;
    protected RestaurantTable $table;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');

        $this->user = User::where('role', 'user')->first();
        $this->dish = Dish::first();
        $this->table = RestaurantTable::first();
    }

    public function test_customer_can_checkout_dine_in_with_table(): void
    {
        $this->actingAs($this->user)
            ->withSession([
                'cart' => [
                    $this->dish->id => ['quantity' => 1, 'special_instructions' => 'Table delivery'],
                ],
            ])
            ->post('/checkout', [
                'order_type' => 'dine_in',
                'table_number' => $this->table->table_number,
                'customer_name' => 'Dine In Guest',
                'customer_phone' => '+855 12 111 222',
                'payment_method' => 'cash_on_delivery',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('orders', [
            'order_type' => 'dine_in',
            'table_number' => $this->table->table_number,
            'customer_name' => 'Dine In Guest',
        ]);

        $this->assertDatabaseHas('payments', [
            'payment_method' => 'cash_on_delivery',
            'status' => 'pending',
        ]);
    }

    public function test_customer_can_cancel_pending_order(): void
    {
        $order = Order::create([
            'user_id' => $this->user->id,
            'order_type' => 'delivery',
            'subtotal' => 20.00,
            'total_amount' => 22.00,
            'payment_method' => 'cash_on_delivery',
            'order_status' => 'pending',
            'customer_name' => 'Alex',
            'customer_phone' => '12345',
            'delivery_address' => 'Phnom Penh',
        ]);

        $this->actingAs($this->user)
            ->post("/orders/{$order->id}/cancel")
            ->assertRedirect()
            ->assertSessionHas('success');

        $order->refresh();
        $this->assertEquals('cancelled', $order->order_status);
    }

    public function test_customer_cannot_cancel_preparing_order(): void
    {
        $order = Order::create([
            'user_id' => $this->user->id,
            'order_type' => 'delivery',
            'subtotal' => 20.00,
            'total_amount' => 22.00,
            'payment_method' => 'cash_on_delivery',
            'order_status' => 'preparing',
            'customer_name' => 'Alex',
            'customer_phone' => '12345',
            'delivery_address' => 'Phnom Penh',
        ]);

        $this->actingAs($this->user)
            ->post("/orders/{$order->id}/cancel")
            ->assertRedirect()
            ->assertSessionHas('error');

        $order->refresh();
        $this->assertEquals('preparing', $order->order_status);
    }
}
