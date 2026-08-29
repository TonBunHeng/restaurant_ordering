<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Dish;
use App\Models\Promotion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PromotionAndDiscountTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $admin;
    protected Dish $dish;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed');

        $this->user = User::where('role', 'user')->first();
        $this->admin = User::where('role', 'super_admin')->first();
        $this->dish = Dish::first();
    }

    public function test_admin_can_create_promotion(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/promotions', [
            'code' => 'SAVE20',
            'name' => 'Save 20 Percent',
            'discount_type' => 'percentage',
            'discount_value' => 20,
            'min_order_amount' => 15.00,
            'max_discount_amount' => 10.00,
            'is_active' => 1,
        ]);

        $response->assertRedirect('/admin/promotions');
        $this->assertDatabaseHas('promotions', [
            'code' => 'SAVE20',
            'discount_value' => 20.00,
        ]);
    }

    public function test_customer_can_apply_valid_promo_to_cart(): void
    {
        Promotion::create([
            'code' => 'DISCOUNT10',
            'discount_type' => 'percentage',
            'discount_value' => 10,
            'min_order_amount' => 10.00,
            'is_active' => true,
        ]);

        $this->actingAs($this->user)
            ->withSession([
                'cart' => [
                    $this->dish->id => ['quantity' => 2, 'special_instructions' => null],
                ],
            ])
            ->post('/cart/promo', ['promo_code' => 'DISCOUNT10'])
            ->assertRedirect()
            ->assertSessionHas('applied_promo', 'DISCOUNT10');
    }

    public function test_customer_cannot_apply_expired_or_invalid_promo(): void
    {
        Promotion::create([
            'code' => 'EXPIRED50',
            'discount_type' => 'percentage',
            'discount_value' => 50,
            'start_date' => now()->subDays(10),
            'end_date' => now()->subDay(),
            'is_active' => true,
        ]);

        $this->actingAs($this->user)
            ->withSession([
                'cart' => [
                    $this->dish->id => ['quantity' => 2, 'special_instructions' => null],
                ],
            ])
            ->post('/cart/promo', ['promo_code' => 'EXPIRED50'])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertNull(session('applied_promo'));
    }

    public function test_checkout_applies_discount_and_increments_usage(): void
    {
        $promo = Promotion::create([
            'code' => 'FLAT5',
            'discount_type' => 'fixed',
            'discount_value' => 5.00,
            'min_order_amount' => 10.00,
            'is_active' => true,
            'times_used' => 0,
        ]);

        $this->actingAs($this->user)
            ->withSession([
                'cart' => [
                    $this->dish->id => ['quantity' => 2, 'special_instructions' => null],
                ],
                'applied_promo' => 'FLAT5',
            ])
            ->post('/checkout', [
                'order_type' => 'takeaway',
                'customer_name' => 'Test Customer',
                'customer_phone' => '+855 12 345 678',
                'payment_method' => 'cash_on_delivery',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('orders', [
            'promo_code' => 'FLAT5',
            'discount_amount' => 5.00,
        ]);

        $promo->refresh();
        $this->assertEquals(1, $promo->times_used);
    }
}
