<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'product_id',
        'quantidade',
        'adicionado_em',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}