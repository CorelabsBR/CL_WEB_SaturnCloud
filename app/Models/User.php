<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'user';

    const CREATED_AT = 'criado_em';
    const UPDATED_AT = 'atualizado_em';

protected $fillable = [
    'nome',
    'email',
    'senha',
    'cpf_cnpj',
    'celular',
    'cep',
    'rua',
    'numero',
    'complemento',
    'bairro',
    'cidade',
    'estado',
    'pais',
    'data_nasc',
    'tipo_usuario',
    'google_id',
    'discord_id',
    'avatar',
    'provider',
    'email_verified_at',
];

protected $hidden = [
    'senha',
];

protected $casts = [
    'email_verified_at' => 'datetime',
    'profile_completed_at' => 'datetime',
    'data_nasc' => 'date',
    'password' => 'hashed',
];

public function getAuthPassword()
{
    return $this->senha;
}
public function hasCompletedProfile(): bool
{
    return !is_null($this->profile_completed_at)
        || (
            filled($this->nome)
            && filled($this->cpf_cnpj)
            && filled($this->celular)
            && filled($this->data_nasc)
            && filled($this->cep)
            && filled($this->rua)
            && filled($this->numero)
            && filled($this->bairro)
            && filled($this->cidade)
            && filled($this->estado)
            && filled($this->pais)
        );
}
}
