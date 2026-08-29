<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Reservation;

class ReservationApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_can_book_table_reservation(): void
    {
        $user = User::where('role', 'user')->first();

        $payload = [
            'guest_name' => 'Alex Rivera',
            'guest_phone' => '+855 12 345 678',
            'reservation_date' => now()->addDays(3)->format('Y-m-d'),
            'reservation_time' => '07:30 PM',
            'guest_count' => 4,
            'table_type' => 'Window City View',
            'special_requests' => 'Quiet corner table',
        ];

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/reservations', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'guest_name' => 'Alex Rivera',
                    'guest_count' => 4,
                ],
            ]);

        $this->assertDatabaseHas('reservations', [
            'guest_name' => 'Alex Rivera',
        ]);
    }

    public function test_admin_can_confirm_reservation(): void
    {
        $admin = User::where('role', 'super_admin')->first();
        $res = Reservation::first();

        $response = $this->actingAs($admin, 'sanctum')->putJson("/api/v1/admin/reservations/{$res->id}/status", [
            'status' => 'confirmed',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'status' => 'confirmed',
                ],
            ]);
    }
}
