<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    /**
     * Store new table reservation.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'guest_name' => 'required|string|max:255',
            'guest_phone' => 'required|string|max:50',
            'guest_email' => 'nullable|email',
            'reservation_date' => 'required|date|after_or_equal:today',
            'reservation_time' => 'required|string|max:20',
            'guest_count' => 'required|integer|min:1|max:50',
            'table_type' => 'nullable|string|max:100',
            'special_requests' => 'nullable|string',
        ]);

        $validated['user_id'] = $request->user('sanctum')?->id;
        $validated['status'] = 'pending';

        $reservation = Reservation::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Table reservation requested successfully! Our team will confirm shortly.',
            'data' => $reservation,
        ], 201);
    }

    /**
     * Get reservations of the authenticated user.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $reservations = $user->reservations()->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $reservations->items(),
            'meta' => [
                'current_page' => $reservations->currentPage(),
                'last_page' => $reservations->lastPage(),
                'per_page' => $reservations->perPage(),
                'total' => $reservations->total(),
            ],
        ]);
    }

    /**
     * Admin: List all table reservations.
     */
    public function adminIndex(Request $request)
    {
        $query = Reservation::latest('reservation_date');

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($date = $request->input('date')) {
            $query->where('reservation_date', $date);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('reservation_number', 'like', "%{$search}%")
                  ->orWhere('guest_name', 'like', "%{$search}%")
                  ->orWhere('guest_phone', 'like', "%{$search}%");
            });
        }

        $reservations = $query->paginate((int) $request->input('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $reservations->items(),
            'meta' => [
                'current_page' => $reservations->currentPage(),
                'last_page' => $reservations->lastPage(),
                'per_page' => $reservations->perPage(),
                'total' => $reservations->total(),
            ],
        ]);
    }

    /**
     * Admin: Update reservation status.
     */
    public function updateStatus(Request $request, Reservation $reservation)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,completed,cancelled',
        ]);

        $reservation->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Reservation status updated',
            'data' => $reservation->fresh(),
        ]);
    }

    /**
     * Delete reservation.
     */
    public function destroy(Reservation $reservation)
    {
        $reservation->delete();

        return response()->json([
            'success' => true,
            'message' => 'Reservation removed',
        ]);
    }
}
