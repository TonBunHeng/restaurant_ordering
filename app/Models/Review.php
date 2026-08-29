<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'dish_id',
        'rating',
        'title',
        'comment',
        'status',
    ];

    protected $casts = [
        'rating' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saved(function ($review) {
            $review->dish?->refreshRatingStats();
        });

        static::deleted(function ($review) {
            $review->dish?->refreshRatingStats();
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function dish()
    {
        return $this->belongsTo(Dish::class);
    }
}
