<?php

namespace Tests\Feature;

use App\Models\Dish;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\RestaurantSetting;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminManagementAndReportsTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $customer;
    protected Dish $dish;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');

        $this->superAdmin = User::where('role', 'super_admin')->first();
        $this->customer = User::where('role', 'user')->first();
        $this->dish = Dish::first();
    }

    public function test_admin_can_view_reports(): void
    {
        $response = $this->actingAs($this->superAdmin)->get('/admin/reports?tab=sales');
        $response->assertStatus(200);
        $response->assertSee('Sales', false);

        $foodRes = $this->actingAs($this->superAdmin)->get('/admin/reports?tab=food');
        $foodRes->assertStatus(200);
        $foodRes->assertSee('Top 10 Most Ordered Dishes');

        $resRes = $this->actingAs($this->superAdmin)->get('/admin/reports?tab=reservations');
        $resRes->assertStatus(200);
        $resRes->assertSee('Table Reservations Report');
    }

    public function test_admin_can_update_settings(): void
    {
        $response = $this->actingAs($this->superAdmin)->post('/admin/settings', [
            'name' => 'Royal Heritage Kitchen',
            'phone' => '+855 23 888 777',
            'email' => 'contact@heritage.com',
            'address' => 'Wat Phnom, Phnom Penh',
            'opening_time' => '09:00',
            'closing_time' => '23:00',
            'tax_percentage' => 10,
            'service_charge_percentage' => 5,
            'delivery_fee' => 2.50,
            'free_delivery_threshold' => 35.00,
            'min_delivery_order' => 12.00,
            'currency' => '$',
            'reservation_duration' => 120,
            'cancellation_window_hours' => 2,
        ]);

        $response->assertRedirect('/admin/settings');
        $this->assertEquals('Royal Heritage Kitchen', RestaurantSetting::get('name'));
    }

    public function test_admin_can_manage_customers_status(): void
    {
        $response = $this->actingAs($this->superAdmin)->put("/admin/customers/{$this->customer->id}/toggle-status");
        $response->assertRedirect();

        $this->customer->refresh();
        $this->assertEquals('suspended', $this->customer->status);

        $this->actingAs($this->superAdmin)->put("/admin/customers/{$this->customer->id}/toggle-status");
        $this->customer->refresh();
        $this->assertEquals('active', $this->customer->status);
    }

    public function test_admin_can_moderate_reviews(): void
    {
        $review = Review::updateOrCreate(
            ['dish_id' => $this->dish->id, 'user_id' => $this->customer->id],
            [
                'rating' => 4,
                'comment' => 'Great food but noisy table.',
                'status' => 'published',
            ]
        );

        $this->actingAs($this->superAdmin)->put("/admin/reviews/{$review->id}/status", [
            'status' => 'hidden',
        ])->assertRedirect();

        $review->refresh();
        $this->assertEquals('hidden', $review->status);
    }

    public function test_super_admin_can_create_staff(): void
    {
        $response = $this->actingAs($this->superAdmin)->post('/admin/users', [
            'name' => 'New Cook',
            'email' => 'cook@example.com',
            'password' => 'secret123',
            'role' => 'staff',
            'status' => 'active',
        ]);

        $response->assertRedirect('/admin/users');
        $this->assertDatabaseHas('users', [
            'email' => 'cook@example.com',
            'role' => 'staff',
        ]);
    }
}
