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
    'email_verification_code',
    'profile_completed_at',
    'timeout_until',
    'timeout_reason',
];

protected $hidden = [
    'senha',
    'email_verification_code',
];

protected $casts = [
    'email_verified_at' => 'datetime',
    'profile_completed_at' => 'datetime',
    'timeout_until' => 'datetime',
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

public function isTimedOut(): bool
{
    return $this->timeout_until !== null && $this->timeout_until->isFuture();
}

public function hasUnlimitedTimeout(): bool
{
    return $this->timeout_until !== null && $this->timeout_until->gte(now()->addYears(50));
}

public function timeoutLabel(): string
{
    if (! $this->isTimedOut()) {
        return 'Sem timeout';
    }

    return $this->hasUnlimitedTimeout()
        ? 'Ilimitado'
        : 'Até ' . $this->timeout_until->format('d/m/Y H:i');
}
}
