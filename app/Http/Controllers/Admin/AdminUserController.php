<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        // Only super_admin can manage users and staff
        if (auth()->user()->role !== 'super_admin') {
            abort(403, 'Super admin privileges required to manage staff and user accounts.');
        }

        $query = User::latest();

        if ($role = $request->input('role')) {
            $query->where('role', $role);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(15)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        if (auth()->user()->role !== 'super_admin') {
            abort(403);
        }

        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        if (auth()->user()->role !== 'super_admin') {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'phone' => 'nullable|string|max:50',
            'password' => ['required', Password::min(6)],
            'role' => 'required|in:user,staff,admin,super_admin',
            'status' => 'required|in:active,suspended',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $user = User::create($validated);

        ActivityLog::log('user_created', "Created new account for {$user->name} with role {$user->role}.", $user);

        return redirect()->route('admin.users.index')->with('success', "Account for {$user->name} created successfully.");
    }

    public function edit(User $user)
    {
        if (auth()->user()->role !== 'super_admin') {
            abort(403);
        }

        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        if (auth()->user()->role !== 'super_admin') {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:50',
            'role' => 'required|in:user,staff,admin,super_admin',
            'status' => 'required|in:active,suspended',
            'password' => ['nullable', Password::min(6)],
        ]);

        if ($user->id === auth()->id() && $validated['role'] !== 'super_admin') {
            return back()->with('error', 'You cannot downgrade your own super admin role.');
        }

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        ActivityLog::log('user_updated', "Updated profile/role for {$user->name}.", $user);

        return redirect()->route('admin.users.index')->with('success', "Account for {$user->name} updated successfully.");
    }

    public function updateRole(Request $request, User $user)
    {
        if (auth()->user()->role !== 'super_admin') {
            abort(403);
        }

        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot change your own super admin role.');
        }

        $validated = $request->validate([
            'role' => 'required|in:user,staff,admin,super_admin',
        ]);

        $user->update(['role' => $validated['role']]);

        ActivityLog::log('user_role_changed', "Changed role of {$user->name} to {$validated['role']}.", $user);

        return back()->with('success', "Updated role for {$user->name} to " . ucfirst($user->role) . '.');
    }

    public function toggleStatus(Request $request, User $user)
    {
        if (auth()->user()->role !== 'super_admin') {
            abort(403);
        }

        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        $newStatus = $user->status === 'active' ? 'suspended' : 'active';
        $user->update(['status' => $newStatus]);

        ActivityLog::log('user_status_changed', "Changed status of {$user->name} to {$newStatus}.", $user);

        return back()->with('success', "Account for {$user->name} is now {$newStatus}.");
    }
}
