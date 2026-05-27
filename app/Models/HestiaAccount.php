<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HestiaAccount extends Model
{
    protected $fillable = [
        'service_id',
        'hestia_user',
        'password',
        'domain',
        'ftp_user',
        'database_name',
        'database_user',
        'raw_response',
    ];

    protected $casts = [
        'password' => 'encrypted',
        'raw_response' => 'array',
    ];

    protected $hidden = [
        'password',
    ];

    public function service()
    {
        return $this->belongsTo(CustomerService::class, 'service_id');
    }
}
