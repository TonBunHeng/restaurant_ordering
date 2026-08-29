<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Reservation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'table_id',
        'reservation_number',
        'guest_name',
        'guest_phone',
        'guest_email',
        'reservation_date',
        'reservation_time',
        'guest_count',
        'table_type',
        'special_requests',
        'status',
    ];

    protected $casts = [
        'reservation_date' => 'date:Y-m-d',
        'guest_count' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($res) {
            if (empty($res->reservation_number)) {
                $res->reservation_number = 'RES-' . strtoupper(Str::random(4)) . '-' . date('ymd');
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function table()
    {
        return $this->belongsTo(RestaurantTable::class, 'table_id');
    }
}
