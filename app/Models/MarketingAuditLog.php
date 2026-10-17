<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketingAuditLog extends Model
{
    public $timestamps = false;
    protected $guarded = ['id'];
    protected $casts = ['changes' => 'array', 'created_at' => 'datetime'];
}
