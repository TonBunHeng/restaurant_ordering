<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RestaurantTable extends Model
{
    use HasFactory;

    protected $table = 'tables';

    public const LOCATIONS = [
        'Main Dining',
        'Window',
        'Outdoor Terrace',
        'VIP Room',
        'Private Booth',
    ];

    public static function getLocations(): array
    {
        return config('restaurant.table_locations', self::LOCATIONS);
    }

    protected $fillable = [
        'table_number',
        'capacity',
        'location',
        'status',
        'description',
    ];

    protected $casts = [
        'capacity' => 'integer',
    ];

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class, 'table_id');
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }
}
