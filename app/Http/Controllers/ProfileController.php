<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        $user = auth()->user();
        $tab = $request->input('tab', 'profile');

        $orders = $user->orders()->with('items.dish')->latest()->paginate(10, ['*'], 'orders_page');
        $reservations = $user->reservations()->with('table')->latest('reservation_date')->paginate(10, ['*'], 'res_page');
        $favorites = $user->favoriteDishes()->with('category')->paginate(12, ['*'], 'fav_page');
        $reviews = $user->reviews()->with('dish')->latest()->paginate(10, ['*'], 'rev_page');

        return view('profile', compact('user', 'tab', 'orders', 'reservations', 'favorites', 'reviews'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:50',
            'bio' => 'nullable|string|max:500',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        if ($request->hasFile('avatar')) {
            // Delete old avatar if exists and stored locally
            if ($user->avatar && str_starts_with($user->avatar, '/uploads/avatars/')) {
                $oldPath = public_path(ltrim($user->avatar, '/'));
                if (file_exists($oldPath)) {
                    @unlink($oldPath);
                }
            }

            $file = $request->file('avatar');
            $filename = 'avatar_' . $user->id . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
            $destinationPath = public_path('uploads/avatars');
            if (!file_exists($destinationPath)) {
                @mkdir($destinationPath, 0755, true);
            }
            $file->move($destinationPath, $filename);
            $validated['avatar'] = '/uploads/avatars/' . $filename;
        }

        $user->update($validated);

        return redirect()->route('profile.show')->with('success', 'Profile information updated successfully.');
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required|current_password',
            'password' => ['required', 'confirmed', Password::min(6)],
        ]);

        auth()->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', 'Password changed successfully.');
    }
}
