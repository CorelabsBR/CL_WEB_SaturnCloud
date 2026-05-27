<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceProvisioningConfiguration extends Model
{
    protected $fillable = [
        'service_id',
        'environment',
        'last_payload',
        'last_error',
    ];

    protected $casts = [
        'environment' => 'array',
        'last_payload' => 'array',
        'last_error' => 'array',
    ];

    public function service()
    {
        return $this->belongsTo(CustomerService::class, 'service_id');
    }
}
