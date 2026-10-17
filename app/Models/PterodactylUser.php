<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PterodactylUser extends Model
{
    protected $fillable = [
        'user_id',
        'pterodactyl_user_id',
        'email',
        'username',
        'encrypted_password',
    ];
}
