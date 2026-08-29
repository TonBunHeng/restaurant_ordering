<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Dish extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'short_description',
        'ingredients',
        'allergens',
        'dietary_info',
        'price',
        'discount_price',
        'preparation_time',
        'calories',
        'spicy_level',
        'is_spicy',
        'is_vegetarian',
        'is_halal',
        'is_chef_special',
        'is_available',
        'cover_image',
        'images',
        'average_rating',
        'reviews_count',
        'status',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'discount_price' => 'decimal:2',
        'preparation_time' => 'integer',
        'calories' => 'integer',
        'spicy_level' => 'integer',
        'is_spicy' => 'boolean',
        'is_vegetarian' => 'boolean',
        'is_halal' => 'boolean',
        'is_chef_special' => 'boolean',
        'is_available' => 'boolean',
        'images' => 'array',
        'average_rating' => 'decimal:2',
        'reviews_count' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($dish) {
            if (empty($dish->slug)) {
                $dish->slug = Str::slug($dish->name) . '-' . Str::random(5);
            }
        });
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class)->where('status', 'published')->latest();
    }

    public function allReviews()
    {
        return $this->hasMany(Review::class);
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published')->where('is_available', true);
    }

    public function scopeChefSpecials($query)
    {
        return $query->published()->where('is_chef_special', true);
    }

    public function getEffectivePriceAttribute(): float
    {
        return (float) ($this->discount_price ?: $this->price);
    }

    public function getRatingAttribute()
    {
        return $this->average_rating;
    }

    public function refreshRatingStats(): void
    {
        $avg = $this->allReviews()->where('status', 'published')->avg('rating') ?: 5.00;
        $count = $this->allReviews()->where('status', 'published')->count();

        $this->updateQuietly([
            'average_rating' => round($avg, 2),
            'reviews_count' => $count,
        ]);
    }
}
