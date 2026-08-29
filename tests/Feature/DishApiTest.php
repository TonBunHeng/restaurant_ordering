<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Category;
use App\Models\Dish;

class DishApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_can_list_published_dishes(): void
    {
        $response = $this->getJson('/api/v1/dishes');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'slug',
                        'price',
                        'category',
                    ],
                ],
                'meta',
            ]);
    }

    public function test_can_filter_dishes_by_vegetarian(): void
    {
        $response = $this->getJson('/api/v1/dishes?is_vegetarian=1');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertNotEmpty($data);
        foreach ($data as $dish) {
            $this->assertTrue((bool) $dish['is_vegetarian']);
        }
    }

    public function test_can_view_single_dish_by_slug(): void
    {
        $dish = Dish::first();
        $response = $this->getJson('/api/v1/dishes/' . $dish->slug);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $dish->id,
                    'name' => $dish->name,
                ],
            ]);
    }

    public function test_admin_can_create_dish(): void
    {
        $admin = User::where('role', 'super_admin')->first();
        $category = Category::first();

        $payload = [
            'category_id' => $category->id,
            'name' => 'Smoked Kampot Duck Breast',
            'description' => 'Tender smoked duck breast glazed with palm sugar and Kampot black pepper.',
            'short_description' => 'Smoked duck breast with palm sugar black pepper glaze.',
            'price' => 18.50,
            'preparation_time' => 25,
            'calories' => 520,
            'is_spicy' => false,
            'is_vegetarian' => false,
            'is_chef_special' => true,
            'is_available' => true,
            'status' => 'published',
        ];

        $response = $this->actingAs($admin, 'sanctum')->postJson('/api/v1/admin/dishes', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'name' => 'Smoked Kampot Duck Breast',
                ],
            ]);

        $this->assertDatabaseHas('dishes', [
            'name' => 'Smoked Kampot Duck Breast',
        ]);
    }

    public function test_regular_user_cannot_create_dish(): void
    {
        $user = User::where('role', 'user')->first();
        $category = Category::first();

        $payload = [
            'category_id' => $category->id,
            'name' => 'Unauthorized Dish',
            'description' => 'Test description',
            'price' => 10.00,
        ];

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/admin/dishes', $payload);
        $response->assertStatus(403);
    }

    public function test_admin_cannot_delete_category_with_existing_dishes(): void
    {
        $admin = User::where('role', 'super_admin')->first();
        $category = Category::whereHas('dishes')->first();

        $response = $this->actingAs($admin, 'sanctum')->deleteJson("/api/v1/admin/categories/{$category->id}");

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
        ]);
    }
}
