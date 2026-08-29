<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'image',
        'icon',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($cat) {
            if (empty($cat->slug)) {
                $cat->slug = Str::slug($cat->name);
            }
        });
    }

    public function dishes()
    {
        return $this->hasMany(Dish::class);
    }

    public function publishedDishes()
    {
        return $this->hasMany(Dish::class)->where('status', 'published')->where('is_available', true);
    }

    public function getIconClassAttribute(): string
    {
        if (!empty($this->icon)) {
            return str_starts_with($this->icon, 'bi-') ? $this->icon : 'bi-' . $this->icon;
        }

        return match ($this->slug) {
            'khmer-specialties' => 'bi-egg-fried',
            'steaks-grills' => 'bi-fire',
            'burgers-sandwiches' => 'bi-circle-square',
            'artisan-pizzas' => 'bi-pie-chart',
            'salads-bowls' => 'bi-flower2',
            'desserts-pastries' => 'bi-cake2',
            'drinks-beverages', 'beverages' => 'bi-cup-hot',
            default => 'bi-card-list',
        };
    }
}