<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = [
        'code',
        'type',
        'value',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
        'value' => 'decimal:2',
    ];

    public function discountFor(float $subtotal): float
    {
        if (! $this->active || $subtotal <= 0) {
            return 0;
        }

        $discount = $this->type === 'percent'
            ? $subtotal * ((float) $this->value / 100)
            : (float) $this->value;

        return round(min($subtotal, max(0, $discount)), 2);
    }
}
