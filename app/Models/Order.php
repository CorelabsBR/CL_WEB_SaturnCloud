<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    const CREATED_AT = 'criado_em';
    const UPDATED_AT = 'atualizado_em';

    protected $fillable = [
        'user_id',
        'total',
        'coupon_code',
        'discount_amount',
        'status',
        'billing_type',
        'service_id',
        'due_at',
        'mp_pagamento_id',
    ];

    protected $casts = [
        'criado_em' => 'datetime',
        'atualizado_em' => 'datetime',
        'due_at' => 'datetime',
        'total' => 'decimal:2',
        'discount_amount' => 'decimal:2',
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function services()
    {
        return $this->hasMany(CustomerService::class);
    }

    public function renewalService()
    {
        return $this->belongsTo(CustomerService::class, 'service_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}   
