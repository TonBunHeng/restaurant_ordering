<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'order_number',
        'order_type',
        'table_number',
        'subtotal',
        'delivery_fee',
        'discount_amount',
        'tax_amount',
        'service_charge',
        'promo_code',
        'total_amount',
        'payment_method',
        'payment_status',
        'order_status',
        'customer_name',
        'customer_phone',
        'customer_email',
        'delivery_address',
        'notes',
        'estimated_prep_time',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'delivery_fee' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'service_charge' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'estimated_prep_time' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $order->order_number = 'ORD-' . strtoupper(Str::random(4)) . '-' . date('ymdHis');
            }
            if (empty($order->estimated_prep_time)) {
                $order->estimated_prep_time = 25;
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }

    public function getFormattedOrderTypeAttribute(): string
    {
        return match ($this->order_type) {
            'dine_in' => 'Dine-in (Table ' . ($this->table_number ?: 'N/A') . ')',
            'takeaway' => 'Takeaway / Pickup',
            'delivery' => 'Delivery',
            default => ucfirst(str_replace('_', ' ', $this->order_type ?? 'delivery')),
        };
    }
}
