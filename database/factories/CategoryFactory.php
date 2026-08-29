<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);
        return [
            'name' => ucfirst($name),
            'slug' => Str::slug($name),
            'icon' => 'Landmark',
            'description' => fake()->paragraph(),
            'image' => 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=800',
            'order' => fake()->numberBetween(1, 10),
            'is_active' => true,
        ];
    }
}
