<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class RestaurantSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'group',
    ];

    public static function get(string $key, $default = null)
    {
        return Cache::rememberForever("restaurant_setting_{$key}", function () use ($key, $default) {
            $record = static::where('key', $key)->first();
            if ($record) {
                return $record->value;
            }
            return config("restaurant.{$key}", $default);
        });
    }

    public static function set(string $key, $value, string $group = 'general'): self
    {
        Cache::forget("restaurant_setting_{$key}");
        Cache::forget('all_restaurant_settings');

        return static::updateOrCreate(
            ['key' => $key],
            ['value' => (string) $value, 'group' => $group]
        );
    }

    public static function getAll(): array
    {
        return Cache::rememberForever('all_restaurant_settings', function () {
            $defaults = [
                'name' => config('restaurant.name', 'Royal Khmer Kitchen'),
                'full_name' => config('restaurant.full_name', 'Royal Khmer Kitchen & Restaurant'),
                'phone' => config('restaurant.phone', '+855 12 888 999'),
                'email' => config('restaurant.email', 'contact@royalkhmerkitchen.kh'),
                'address' => config('restaurant.address', 'Street 240, Daun Penh, Phnom Penh'),
                'currency' => config('restaurant.currency', '$'),
                'tax_percentage' => '10',
                'service_charge_percentage' => '5',
                'delivery_fee' => (string) config('restaurant.delivery_fee', 2.00),
                'free_delivery_threshold' => (string) config('restaurant.free_delivery_threshold', 30.00),
                'min_delivery_order' => '10.00',
                'opening_time' => '10:00',
                'closing_time' => '22:00',
                'reservation_duration' => '120', // in minutes
                'cancellation_window_hours' => '2',
                'description' => config('restaurant.description', 'Simple, fresh and authentic dining hand-crafted daily.'),
            ];

            $dbSettings = static::all()->pluck('value', 'key')->toArray();
            return array_merge($defaults, $dbSettings);
        });
    }
}
