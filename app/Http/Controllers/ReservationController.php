<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\RestaurantSetting;
use App\Models\RestaurantTable;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReservationController extends Controller
{
    public function index()
    {
        $reservations = auth()->user()->reservations()->with('table')->latest('reservation_date')->paginate(10);

        return view('reservations.index', compact('reservations'));
    }

    public function create(Request $request)
    {
        $date = $request->input('date', now()->addDay()->format('Y-m-d'));
        $time = $request->input('time', '19:00');
        $guests = (int) $request->input('guests', 2);

        // Fetch tables that are available and can fit the requested guests
        $allTables = RestaurantTable::where('status', '!=', 'unavailable')
            ->where('capacity', '>=', $guests)
            ->orderBy('capacity', 'asc')
            ->orderBy('table_number', 'asc')
            ->get();

        // Check which tables are already reserved for this date & time
        $bookedTableIds = Reservation::where('reservation_date', $date)
            ->where('reservation_time', $time)
            ->whereNotIn('status', ['cancelled', 'rejected'])
            ->pluck('table_id')
            ->filter()
            ->toArray();

        $availableTables = $allTables->filter(function ($table) use ($bookedTableIds) {
            return !in_array($table->id, $bookedTableIds);
        });

        $user = auth()->user();
        $timeSlots = config('restaurant.time_slots', ['11:30', '12:00', '12:30', '13:00', '13:30', '17:30', '18:00', '18:30', '19:00', '19:30', '20:00', '20:30', '21:00']);
        $openingTime = RestaurantSetting::get('opening_time', '10:00');
        $closingTime = RestaurantSetting::get('closing_time', '22:00');

        return view('reservations.create', compact(
            'date',
            'time',
            'guests',
            'allTables',
            'availableTables',
            'bookedTableIds',
            'user',
            'timeSlots',
            'openingTime',
            'closingTime'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'table_id' => 'required|exists:tables,id',
            'reservation_date' => 'required|date|after_or_equal:today',
            'reservation_time' => 'required|string|max:20',
            'guest_count' => 'required|integer|min:1|max:50',
            'guest_name' => 'required|string|max:255',
            'guest_phone' => 'required|string|max:50',
            'guest_email' => 'nullable|email',
            'special_requests' => 'nullable|string|max:500',
        ]);

        // Opening and closing time validation
        $openingTime = RestaurantSetting::get('opening_time', '10:00');
        $closingTime = RestaurantSetting::get('closing_time', '22:00');
        $resTime = $validated['reservation_time'];

        if ($resTime < $openingTime || $resTime > $closingTime) {
            return back()->with('error', "Reservations can only be made within opening hours ({$openingTime} - {$closingTime}).")->withInput();
        }

        // Check if requested datetime is in the past
        $todayStr = now()->format('Y-m-d');
        if ($validated['reservation_date'] === $todayStr && $resTime <= now()->format('H:i')) {
            return back()->with('error', 'Cannot book a reservation time in the past today. Please select an upcoming time slot.')->withInput();
        }

        try {
            $reservation = DB::transaction(function () use ($validated, $request) {
                $table = RestaurantTable::lockForUpdate()->findOrFail($validated['table_id']);

                if ($table->status === 'unavailable') {
                    throw new \Exception("Table '{$table->table_number}' is currently unavailable for booking.");
                }

                if ($validated['guest_count'] > $table->capacity) {
                    throw new \Exception("Guest count ({$validated['guest_count']}) exceeds the capacity of {$table->table_number} (maximum {$table->capacity} seats).");
                }

                // Double booking prevention check with lock
                $alreadyBooked = Reservation::where('table_id', $table->id)
                    ->where('reservation_date', $validated['reservation_date'])
                    ->where('reservation_time', $validated['reservation_time'])
                    ->whereNotIn('status', ['cancelled', 'rejected'])
                    ->lockForUpdate()
                    ->exists();

                if ($alreadyBooked) {
                    throw new \Exception("Table '{$table->table_number}' is already reserved for {$validated['reservation_date']} at {$validated['reservation_time']}. Please select another table or time.");
                }

                return Reservation::create([
                    'user_id' => auth()->id(),
                    'table_id' => $table->id,
                    'guest_name' => $validated['guest_name'],
                    'guest_phone' => $validated['guest_phone'],
                    'guest_email' => $validated['guest_email'] ?? auth()->user()?->email,
                    'reservation_date' => $validated['reservation_date'],
                    'reservation_time' => $validated['reservation_time'],
                    'guest_count' => $validated['guest_count'],
                    'table_type' => $table->location,
                    'special_requests' => $validated['special_requests'] ?? null,
                    'status' => 'pending',
                ]);
            });

            return redirect()->route('reservations.show', $reservation->id)
                ->with('success', 'Your table reservation request has been submitted successfully! We will confirm it shortly.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function show(Reservation $reservation)
    {
        $currentUser = auth()->user();

        // Enforce ownership: customers can only view their own reservations
        if ($reservation->user_id && (!$currentUser || ($reservation->user_id !== $currentUser->id && !$currentUser->isAdmin()))) {
            abort(403, 'Unauthorized access to this reservation.');
        }

        $reservation->load('table');

        return view('reservations.show', compact('reservation'));
    }

    public function cancel(Request $request, Reservation $reservation)
    {
        $currentUser = auth()->user();

        if ($reservation->user_id !== $currentUser->id && !$currentUser->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        if (in_array($reservation->status, ['completed', 'cancelled', 'rejected'])) {
            return back()->with('error', 'This reservation cannot be cancelled in its current status.');
        }

        // Check cancellation policy if not admin
        if (!$currentUser->isAdmin()) {
            $cancellationHours = (int) RestaurantSetting::get('cancellation_window_hours', 2);
            $resDateTime = Carbon::parse($reservation->reservation_date->format('Y-m-d') . ' ' . $reservation->reservation_time);
            
            if (now()->diffInHours($resDateTime, false) < $cancellationHours) {
                return back()->with('error', "Reservations can only be cancelled at least {$cancellationHours} hours before the reserved time. Please contact restaurant support directly.");
            }
        }

        $reservation->update(['status' => 'cancelled']);

        return back()->with('success', 'Reservation # ' . $reservation->reservation_number . ' has been cancelled.');
    }
}
