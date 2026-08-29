<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Place;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PlaceFactory extends Factory
{
    protected $model = Place::class;

    public function definition(): array
    {
        $name = fake()->unique()->city() . ' Temple';
        return [
            'category_id' => Category::factory(),
            'name' => $name,
            'slug' => Str::slug($name) . '-' . fake()->unique()->numberBetween(100, 999),
            'description' => fake()->paragraphs(3, true),
            'short_description' => fake()->sentence(),
            'province' => fake()->randomElement(['Siem Reap', 'Phnom Penh', 'Battambang', 'Kampot', 'Kep']),
            'district' => fake()->word(),
            'address' => fake()->address(),
            'latitude' => fake()->latitude(10, 14),
            'longitude' => fake()->longitude(103, 107),
            'opening_hours' => '08:00 AM - 05:00 PM',
            'entrance_fee' => fake()->randomElement([0, 5, 10, 20, 37]),
            'contact' => fake()->phoneNumber(),
            'website' => fake()->url(),
            'cover_image' => 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=1200',
            'images' => [
                'https://images.unsplash.com/photo-1544644181-1484b3fdfc62?w=800',
            ],
            'average_rating' => fake()->randomFloat(2, 4.0, 5.0),
            'reviews_count' => fake()->numberBetween(1, 100),
            'featured' => fake()->boolean(40),
            'status' => 'published',
        ];
    }
}
