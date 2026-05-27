<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PterodactylEgg extends Model
{
    protected $fillable = [
        'egg_id',
        'nest_id',
        'name',
        'startup',
        'docker_image',
        'docker_images',
    ];

    protected $casts = [
        'egg_id' => 'integer',
        'nest_id' => 'integer',
        'docker_images' => 'array',
    ];

    public function variables()
    {
        return $this->hasMany(PterodactylEggVariable::class, 'pterodactyl_egg_id');
    }

    public function requiredVariables()
    {
        return $this->variables()->where('required', true);
    }
}
