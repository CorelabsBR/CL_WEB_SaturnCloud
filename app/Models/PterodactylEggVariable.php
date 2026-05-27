<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PterodactylEggVariable extends Model
{
    protected $fillable = [
        'pterodactyl_egg_id',
        'pterodactyl_variable_id',
        'name',
        'env_variable',
        'default_value',
        'rules',
        'required',
        'user_viewable',
        'user_editable',
    ];

    protected $casts = [
        'pterodactyl_variable_id' => 'integer',
        'required' => 'boolean',
        'user_viewable' => 'boolean',
        'user_editable' => 'boolean',
    ];

    public function egg()
    {
        return $this->belongsTo(PterodactylEgg::class, 'pterodactyl_egg_id');
    }
}
