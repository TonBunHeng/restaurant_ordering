<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Reservation;
use App\Models\RestaurantTable;
use Illuminate\Http\Request;

class AdminReservationController extends Controller
{
    public function index(Request $request)
    {
        $query = Reservation::with(['table', 'user'])->latest('reservation_date');

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

        $reservations = $query->paginate(15)->withQueryString();

        return view('admin.reservations.index', compact('reservations'));
    }

    public function show(Reservation $reservation)
    {
        $reservation->load(['table', 'user']);
        $tables = RestaurantTable::where('status', '!=', 'unavailable')->orderBy('table_number', 'asc')->get();

        return view('admin.reservations.show', compact('reservation', 'tables'));
    }

    public function updateStatus(Request $request, Reservation $reservation)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,rejected,completed,cancelled',
            'table_id' => 'nullable|exists:tables,id',
        ]);

        if (!empty($validated['table_id']) && $validated['table_id'] != $reservation->table_id) {
            $targetTable = RestaurantTable::findOrFail($validated['table_id']);
            if ($reservation->guest_count > $targetTable->capacity) {
                return back()->with('error', "Cannot assign {$targetTable->table_number} because guest count ({$reservation->guest_count}) exceeds table capacity ({$targetTable->capacity}).");
            }
            $reservation->table_id = $targetTable->id;
            $reservation->table_type = $targetTable->location;
        }

        $oldStatus = $reservation->status;
        $reservation->status = $validated['status'];
        $reservation->save();

        ActivityLog::log('reservation_status_update', "Updated Reservation #{$reservation->reservation_number} from {$oldStatus} to {$validated['status']}.", $reservation);

        return back()->with('success', "Reservation #{$reservation->reservation_number} updated to " . ucfirst($reservation->status) . '.');
    }
}
