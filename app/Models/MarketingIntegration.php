<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketingIntegration extends Model
{
    protected $guarded = ['id'];
    protected $hidden = ['credentials'];
    protected $casts = ['enabled' => 'boolean', 'credentials' => 'encrypted:array', 'permissions' => 'array', 'last_synced_at' => 'datetime'];
}
