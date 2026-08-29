<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Dish;
use App\Models\Order;
use App\Models\Reservation;
use App\Models\RestaurantTable;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebFullStackTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_customer_can_register_and_log_in_via_web(): void
    {
        $response = $this->post('/register', [
            'name' => 'Charlie Diner',
            'email' => 'charlie@example.com',
            'phone' => '+855 12 333 444',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertAuthenticated();

        $this->post('/logout')->assertRedirect(route('home'));
        $this->assertGuest();

        $loginResponse = $this->post('/login', [
            'email' => 'charlie@example.com',
            'password' => 'password123',
        ]);

        $loginResponse->assertRedirect(route('home'));
        $this->assertAuthenticated();
    }

    public function test_customer_can_browse_menu_and_view_dish(): void
    {
        $dish = Dish::first();

        $menuResponse = $this->get('/menu');
        $menuResponse->assertStatus(200);
        $menuResponse->assertSee($dish->name);

        $dishResponse = $this->get('/menu/' . $dish->slug);
        $dishResponse->assertStatus(200);
        $dishResponse->assertSee($dish->name);
    }

    public function test_customer_cart_session_management(): void
    {
        $dish = Dish::first();

        $addResponse = $this->post('/cart/add', [
            'dish_id' => $dish->id,
            'quantity' => 2,
            'special_instructions' => 'Extra sauce',
        ]);

        $addResponse->assertRedirect(route('cart.index'));
        $this->assertEquals(2, session('cart')[$dish->id]['quantity']);

        // Update quantity
        $updateResponse = $this->post('/cart/update', [
            'dish_id' => $dish->id,
            'quantity' => 3,
        ]);
        $updateResponse->assertRedirect();
        $this->assertEquals(3, session('cart')[$dish->id]['quantity']);

        // Remove item
        $removeResponse = $this->post('/cart/remove', [
            'dish_id' => $dish->id,
        ]);
        $removeResponse->assertRedirect();
        $this->assertArrayNotHasKey($dish->id, session('cart', []));
    }

    public function test_customer_can_checkout_and_place_order(): void
    {
        $user = User::where('role', 'user')->first();
        $dish = Dish::first();

        $this->actingAs($user)
            ->withSession([
                'cart' => [
                    $dish->id => ['quantity' => 2, 'special_instructions' => 'Mild'],
                ],
            ])
            ->post('/checkout', [
                'customer_name' => 'Alex Rivera',
                'customer_phone' => '+855 12 999 888',
                'delivery_address' => 'Street 240, Phnom Penh',
                'payment_method' => 'cash_on_delivery',
                'notes' => 'Ring doorbell',
            ])
            ->assertRedirect();

        $this->assertEmpty(session('cart'));
        $this->assertDatabaseHas('orders', [
            'customer_name' => 'Alex Rivera',
            'user_id' => $user->id,
        ]);
    }

    public function test_order_idor_protection(): void
    {
        $owner = User::where('role', 'user')->first();
        $otherUser = User::create([
            'name' => 'Stranger',
            'email' => 'stranger@example.com',
            'password' => bcrypt('password123'),
            'role' => 'user',
            'status' => 'active',
        ]);

        $order = Order::create([
            'user_id' => $owner->id,
            'subtotal' => 25.00,
            'delivery_fee' => 2.00,
            'total_amount' => 27.00,
            'payment_method' => 'cash_on_delivery',
            'payment_status' => 'pending',
            'order_status' => 'pending',
            'customer_name' => 'Alex Rivera',
            'customer_phone' => '+855 12 999 888',
            'delivery_address' => 'Phnom Penh',
        ]);

        // Stranger should receive 403 Forbidden
        $this->actingAs($otherUser)->get('/orders/' . $order->id)->assertStatus(403);

        // Owner should see order receipt
        $this->actingAs($owner)->get('/orders/' . $order->id)->assertStatus(200);
    }

    public function test_table_reservation_and_double_booking_prevention(): void
    {
        $user = User::where('role', 'user')->first();
        $table = RestaurantTable::where('capacity', '>=', 4)->first();

        $bookingDate = now()->addDays(3)->format('Y-m-d');
        $bookingTime = '19:00';

        // 1. Initial reservation succeeds
        $response1 = $this->actingAs($user)->post('/reservations', [
            'table_id' => $table->id,
            'reservation_date' => $bookingDate,
            'reservation_time' => $bookingTime,
            'guest_count' => 4,
            'guest_name' => 'Alex Rivera',
            'guest_phone' => '+855 12 999 888',
        ]);

        $response1->assertRedirect();
        $this->assertDatabaseHas('reservations', [
            'table_id' => $table->id,
            'reservation_date' => $bookingDate,
            'reservation_time' => $bookingTime,
            'guest_name' => 'Alex Rivera',
        ]);

        // 2. Conflicting reservation on same table, date & time is rejected
        $response2 = $this->actingAs($user)->post('/reservations', [
            'table_id' => $table->id,
            'reservation_date' => $bookingDate,
            'reservation_time' => $bookingTime,
            'guest_count' => 2,
            'guest_name' => 'Another Guest',
            'guest_phone' => '+855 99 888 777',
        ]);

        $response2->assertSessionHas('error');
    }

    public function test_guest_count_cannot_exceed_table_capacity(): void
    {
        $user = User::where('role', 'user')->first();
        $smallTable = RestaurantTable::where('capacity', 2)->first();

        $response = $this->actingAs($user)->post('/reservations', [
            'table_id' => $smallTable->id,
            'reservation_date' => now()->addDays(2)->format('Y-m-d'),
            'reservation_time' => '18:00',
            'guest_count' => 8, // Exceeds table capacity of 2
            'guest_name' => 'Alex Rivera',
            'guest_phone' => '+855 12 999 888',
        ]);

        $response->assertSessionHas('error');
    }

    public function test_customer_cannot_access_admin_portal(): void
    {
        $customer = User::where('role', 'user')->first();

        // Customer trying to access admin dashboard directly receives 403 Forbidden
        $response = $this->actingAs($customer)->get('/admin/dashboard');
        $response->assertStatus(403);

        $responseOrders = $this->actingAs($customer)->get('/admin/orders');
        $responseOrders->assertStatus(403);
    }

    public function test_admin_can_manage_tables_and_orders(): void
    {
        $admin = User::where('role', 'super_admin')->first();

        // Admin view dashboard
        $this->actingAs($admin)->get('/admin/dashboard')->assertStatus(200);

        // Admin create table
        $this->actingAs($admin)->post('/admin/tables', [
            'table_number' => 'Table 99',
            'capacity' => 6,
            'location' => 'VIP Room',
            'status' => 'available',
            'description' => 'Brand new table',
        ])->assertRedirect(route('admin.tables.index'));

        $this->assertDatabaseHas('tables', ['table_number' => 'Table 99']);

        // Admin update order status
        $order = Order::first();
        $this->actingAs($admin)->put("/admin/orders/{$order->id}/status", [
            'order_status' => 'preparing',
            'payment_status' => 'paid',
        ])->assertRedirect();

        $this->assertEquals('preparing', $order->fresh()->order_status);
        $this->assertEquals('paid', $order->fresh()->payment_status);
    }
}
