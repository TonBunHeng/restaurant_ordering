<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\RestaurantSetting;
use Illuminate\Http\Request;

class AdminSettingController extends Controller
{
    public function index()
    {
        $settings = RestaurantSetting::getAll();
        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'full_name' => 'nullable|string|max:255',
            'phone' => 'required|string|max:50',
            'email' => 'required|email|max:255',
            'address' => 'required|string|max:500',
            'currency' => 'required|string|max:10',
            'opening_time' => 'required|string|max:10',
            'closing_time' => 'required|string|max:10',
            'tax_percentage' => 'required|numeric|min:0|max:100',
            'service_charge_percentage' => 'required|numeric|min:0|max:100',
            'delivery_fee' => 'required|numeric|min:0',
            'free_delivery_threshold' => 'required|numeric|min:0',
            'min_delivery_order' => 'required|numeric|min:0',
            'reservation_duration' => 'required|integer|min:30|max:480',
            'cancellation_window_hours' => 'required|integer|min:0|max:72',
            'description' => 'nullable|string|max:1000',
        ]);

        foreach ($validated as $key => $val) {
            RestaurantSetting::set($key, $val);
        }

        ActivityLog::log('settings_updated', 'Updated restaurant business settings and parameters.');

        return redirect()->route('admin.settings.index')->with('success', 'Restaurant settings saved successfully.');
    }
}
