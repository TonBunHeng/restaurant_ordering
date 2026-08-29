<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Order;
use App\Models\Reservation;
use App\Models\RestaurantTable;
use Illuminate\Http\Request;

class AdminTableController extends Controller
{
    public function index(Request $request)
    {
        $query = RestaurantTable::withCount(['reservations' => function ($q) {
            $q->whereIn('status', ['pending', 'confirmed'])->where('reservation_date', '>=', now()->format('Y-m-d'));
        }])->orderBy('table_number', 'asc');

        if ($location = $request->input('location')) {
            $query->where('location', $location);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $tables = $query->paginate(15)->withQueryString();
        $locations = RestaurantTable::getLocations();

        return view('admin.tables.index', compact('tables', 'locations'));
    }

    public function map()
    {
        $tables = RestaurantTable::with(['reservations' => function ($q) {
            $q->where('reservation_date', now()->format('Y-m-d'))
              ->whereIn('status', ['pending', 'confirmed']);
        }])->orderBy('table_number', 'asc')->get();

        $activeDineInOrders = Order::where('order_type', 'dine_in')
            ->whereIn('order_status', ['pending', 'confirmed', 'preparing', 'ready'])
            ->get()
            ->keyBy('table_number');

        return view('admin.tables.map', compact('tables', 'activeDineInOrders'));
    }

    public function create()
    {
        $locations = RestaurantTable::getLocations();
        return view('admin.tables.create', compact('locations'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'table_number' => 'required|string|max:50|unique:tables,table_number',
            'capacity' => 'required|integer|min:1|max:50',
            'location' => 'required|string|max:100',
            'status' => 'required|in:available,reserved,occupied,unavailable',
            'description' => 'nullable|string|max:500',
        ]);

        $table = RestaurantTable::create($validated);

        ActivityLog::log('table_created', "Created dining table #{$table->table_number} (Capacity: {$table->capacity}).", $table);

        return redirect()->route('admin.tables.index')->with('success', 'Dining table added successfully.');
    }

    public function edit(RestaurantTable $table)
    {
        $locations = RestaurantTable::getLocations();
        return view('admin.tables.edit', compact('table', 'locations'));
    }

    public function update(Request $request, RestaurantTable $table)
    {
        $validated = $request->validate([
            'table_number' => 'required|string|max:50|unique:tables,table_number,' . $table->id,
            'capacity' => 'required|integer|min:1|max:50',
            'location' => 'required|string|max:100',
            'status' => 'required|in:available,reserved,occupied,unavailable',
            'description' => 'nullable|string|max:500',
        ]);

        $table->update($validated);

        ActivityLog::log('table_updated', "Updated dining table #{$table->table_number} details.", $table);

        return redirect()->route('admin.tables.index')->with('success', 'Table information updated successfully.');
    }

    public function destroy(RestaurantTable $table)
    {
        $activeReservations = $table->reservations()
            ->whereIn('status', ['pending', 'confirmed'])
            ->where('reservation_date', '>=', now()->format('Y-m-d'))
            ->count();

        if ($activeReservations > 0) {
            return back()->with('error', "Cannot delete {$table->table_number} because it has {$activeReservations} upcoming active reservations. Please reassign or cancel those bookings first.");
        }

        ActivityLog::log('table_deleted', "Deleted dining table #{$table->table_number}.", $table);

        $table->delete();

        return redirect()->route('admin.tables.index')->with('success', 'Table deleted successfully.');
    }
}
