<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;

class AdminCustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', 'user')->withCount(['orders', 'reservations', 'reviews'])->latest();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $customers = $query->paginate(15)->withQueryString();

        return view('admin.customers.index', compact('customers'));
    }

    public function show(User $customer)
    {
        $customer->loadCount(['orders', 'reservations', 'reviews']);
        $orders = $customer->orders()->with('items')->latest()->paginate(5, ['*'], 'orders_page');
        $reservations = $customer->reservations()->with('table')->latest('reservation_date')->paginate(5, ['*'], 'res_page');

        return view('admin.customers.show', compact('customer', 'orders', 'reservations'));
    }

    public function toggleStatus(User $customer)
    {
        $newStatus = $customer->status === 'active' ? 'suspended' : 'active';
        $customer->update(['status' => $newStatus]);

        ActivityLog::log('customer_status_toggle', "Set customer {$customer->name} status to {$newStatus}.", $customer);

        return back()->with('success', "Customer {$customer->name} status changed to {$newStatus}.");
    }
}
