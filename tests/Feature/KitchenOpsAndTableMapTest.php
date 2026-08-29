<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Dish;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\RestaurantTable;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KitchenOpsAndTableMapTest extends TestCase
{
    use RefreshDatabase;

    protected User $staff;
    protected User $customer;
    protected Dish $dish;
    protected RestaurantTable $table;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');

        $this->staff = User::where('role', 'staff')->first() ?? User::create([
            'name' => 'Kitchen Staff',
            'email' => 'kitchen@example.com',
            'password' => bcrypt('password123'),
            'role' => 'staff',
            'status' => 'active',
        ]);

        $this->customer = User::where('role', 'user')->first();
        $this->dish = Dish::first();
        $this->table = RestaurantTable::first();
    }

    public function test_staff_can_view_kitchen_queue(): void
    {
        $order = Order::create([
            'user_id' => $this->customer->id,
            'order_type' => 'dine_in',
            'table_number' => $this->table->table_number,
            'subtotal' => 15.00,
            'total_amount' => 15.00,
            'payment_method' => 'cash_on_delivery',
            'order_status' => 'confirmed',
            'customer_name' => 'Kitchen Guest',
            'customer_phone' => '123',
            'delivery_address' => 'Table 1',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'dish_id' => $this->dish->id,
            'dish_name' => $this->dish->name,
            'quantity' => 2,
            'unit_price' => 7.50,
            'subtotal_price' => 15.00,
        ]);

        $response = $this->actingAs($this->staff)->get('/admin/kitchen');
        $response->assertStatus(200);
        $response->assertSee('Live Kitchen Order Display');
        $response->assertSee($order->order_number);
    }

    public function test_staff_can_progress_order_status_and_logs_activity(): void
    {
        $order = Order::create([
            'user_id' => $this->customer->id,
            'order_type' => 'dine_in',
            'subtotal' => 15.00,
            'total_amount' => 15.00,
            'payment_method' => 'cash_on_delivery',
            'order_status' => 'confirmed',
            'customer_name' => 'Kitchen Guest',
            'customer_phone' => '123',
            'delivery_address' => 'Table 1',
        ]);

        $response = $this->actingAs($this->staff)->put("/admin/kitchen/{$order->id}/status", [
            'order_status' => 'preparing',
        ]);

        $response->assertRedirect();
        $order->refresh();
        $this->assertEquals('preparing', $order->order_status);

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->staff->id,
            'action' => 'kitchen_status_update',
        ]);
    }

    public function test_staff_can_view_table_floor_map(): void
    {
        $response = $this->actingAs($this->staff)->get('/admin/tables/map');
        $response->assertStatus(200);
        $response->assertSee('Dining Tables Floor Plan');
        $response->assertSee($this->table->table_number);
    }
}
