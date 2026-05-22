<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HestiaAccount extends Model
{
    protected $fillable = [
        'service_id',
        'hestia_user',
        'domain',
        'ftp_user',
        'database_name',
        'database_user',
        'raw_response',
    ];

    protected $casts = [
        'raw_response' => 'array',
    ];

    public function service()
    {
        return $this->belongsTo(CustomerService::class, 'service_id');
    }
}
