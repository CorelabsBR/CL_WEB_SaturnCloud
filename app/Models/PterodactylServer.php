<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PterodactylServer extends Model
{
    protected $fillable = [
        'service_id',
        'plan_id',
        'pterodactyl_user_id',
        'pterodactyl_server_id',
        'external_id',
        'provision_identifier',
        'uuid',
        'identifier',
        'allocation_id',
        'node_id',
        'egg_id',
        'raw_response',
    ];

    protected $casts = [
        'raw_response' => 'array',
    ];

    public function service()
    {
        return $this->belongsTo(CustomerService::class, 'service_id');
    }

    public function egg()
    {
        return $this->belongsTo(PterodactylEgg::class, 'egg_id', 'egg_id');
    }
}
