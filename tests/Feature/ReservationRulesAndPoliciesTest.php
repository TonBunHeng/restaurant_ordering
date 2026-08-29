<?php

namespace Tests\Feature;

use App\Models\Reservation;
use App\Models\RestaurantSetting;
use App\Models\RestaurantTable;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReservationRulesAndPoliciesTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected RestaurantTable $table;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');

        $this->user = User::where('role', 'user')->first();
        $this->table = RestaurantTable::where('capacity', '>=', 4)->first();
    }

    public function test_cannot_book_outside_opening_hours(): void
    {
        $futureDate = now()->addDays(2)->format('Y-m-d');

        $response = $this->actingAs($this->user)->post('/reservations', [
            'table_id' => $this->table->id,
            'reservation_date' => $futureDate,
            'reservation_time' => '04:00', // 4 AM is outside 10:00 - 22:00
            'guest_count' => 2,
            'guest_name' => 'Early Bird',
            'guest_phone' => '12345',
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('reservations', [
            'guest_name' => 'Early Bird',
        ]);
    }

    public function test_cannot_cancel_reservation_less_than_two_hours_before(): void
    {
        // 1 hour in the future
        $soonTime = now()->addMinutes(45);

        $reservation = Reservation::create([
            'user_id' => $this->user->id,
            'table_id' => $this->table->id,
            'guest_name' => 'Late Canceller',
            'guest_phone' => '12345',
            'guest_count' => 2,
            'reservation_date' => $soonTime->toDateString(),
            'reservation_time' => $soonTime->format('H:i'),
            'status' => 'confirmed',
        ]);

        $response = $this->actingAs($this->user)->post("/reservations/{$reservation->id}/cancel");
        $response->assertSessionHas('error');

        $reservation->refresh();
        $this->assertEquals('confirmed', $reservation->status);
    }

    public function test_can_cancel_reservation_more_than_two_hours_before(): void
    {
        $futureTime = now()->addDays(1)->setTime(18, 0);

        $reservation = Reservation::create([
            'user_id' => $this->user->id,
            'table_id' => $this->table->id,
            'guest_name' => 'Early Canceller',
            'guest_phone' => '12345',
            'guest_count' => 2,
            'reservation_date' => $futureTime->toDateString(),
            'reservation_time' => $futureTime->format('H:i'),
            'status' => 'confirmed',
        ]);

        $response = $this->actingAs($this->user)->post("/reservations/{$reservation->id}/cancel");
        $response->assertSessionHas('success');

        $reservation->refresh();
        $this->assertEquals('cancelled', $reservation->status);
    }
}
