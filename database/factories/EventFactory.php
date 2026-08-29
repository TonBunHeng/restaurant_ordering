<?php

namespace Database\Factories;

use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class EventFactory extends Factory
{
    protected $model = Event::class;

    public function definition(): array
    {
        $title = fake()->sentence(3);
        return [
            'title' => $title,
            'slug' => Str::slug($title) . '-' . fake()->unique()->numberBetween(100, 999),
            'description' => fake()->paragraphs(2, true),
            'short_description' => fake()->sentence(),
            'location' => fake()->streetAddress(),
            'province' => fake()->randomElement(['Siem Reap', 'Phnom Penh', 'Kampot', 'Kep']),
            'start_date' => now()->addDays(fake()->numberBetween(5, 60)),
            'end_date' => now()->addDays(fake()->numberBetween(61, 65)),
            'image' => 'https://images.unsplash.com/photo-1514525253161-7a46d19cd819?w=800',
            'organizer' => fake()->company(),
            'website' => fake()->url(),
            'ticket_price' => fake()->randomElement([0, 10, 25, 50]),
            'featured' => fake()->boolean(30),
            'status' => 'upcoming',
        ];
    }
}
