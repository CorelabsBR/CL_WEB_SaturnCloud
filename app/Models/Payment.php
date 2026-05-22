<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'order_id',
        'provider',
        'provider_payment_id',
        'preference_id',
        'payment_method_id',
        'payment_type_id',
        'amount',
        'status',
        'raw_payload',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'raw_payload' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
