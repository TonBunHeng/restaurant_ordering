<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'discount_type',
        'discount_value',
        'min_order_amount',
        'max_discount_amount',
        'start_date',
        'end_date',
        'usage_limit',
        'times_used',
        'is_active',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'max_discount_amount' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'usage_limit' => 'integer',
        'times_used' => 'integer',
        'is_active' => 'boolean',
    ];

    public function isValidForAmount(float $subtotal, ?string &$errorMessage = null): bool
    {
        if (!$this->is_active) {
            $errorMessage = 'This promo code is currently inactive.';
            return false;
        }

        $today = now()->format('Y-m-d');
        if ($this->start_date && $this->start_date->format('Y-m-d') > $today) {
            $errorMessage = 'This promo code is not active yet.';
            return false;
        }

        if ($this->end_date && $this->end_date->format('Y-m-d') < $today) {
            $errorMessage = 'This promo code has expired.';
            return false;
        }

        if ($this->usage_limit !== null && $this->times_used >= $this->usage_limit) {
            $errorMessage = 'This promo code has reached its maximum usage limit.';
            return false;
        }

        if ($this->min_order_amount > 0 && $subtotal < $this->min_order_amount) {
            $errorMessage = sprintf('This promo code requires a minimum order subtotal of $%0.2f.', $this->min_order_amount);
            return false;
        }

        return true;
    }

    public function calculateDiscount(float $subtotal): float
    {
        if ($this->discount_type === 'percentage') {
            $discount = ($subtotal * $this->discount_value) / 100;
        } else {
            $discount = min($this->discount_value, $subtotal);
        }

        if ($this->max_discount_amount !== null && $this->max_discount_amount > 0) {
            $discount = min($discount, (float) $this->max_discount_amount);
        }

        return round(max(0, $discount), 2);
    }
}
