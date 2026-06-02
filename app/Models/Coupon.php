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
        'usage_limit',
        'used_count',
    ];

    protected $casts = [
        'active' => 'boolean',
        'value' => 'decimal:2',
        'usage_limit' => 'integer',
        'used_count' => 'integer',
    ];

    public function isAvailable(): bool
    {
        if (! $this->active) {
            return false;
        }

        if ($this->isSingleUsePerUser()) {
            return true;
        }

        return $this->usage_limit === null
            || $this->used_count < $this->usage_limit;
    }

    public function isSingleUsePerUser(): bool
    {
        return (int) $this->usage_limit === 1;
    }

    public function discountFor(float $subtotal): float
    {
        if (! $this->isAvailable() || $subtotal <= 0) {
            return 0;
        }

        $discount = $this->type === 'percent'
            ? $subtotal * ((float) $this->value / 100)
            : (float) $this->value;

        return round(min($subtotal, max(0, $discount)), 2);
    }
}
