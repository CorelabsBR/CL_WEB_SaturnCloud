<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $table = 'plans';
    protected $primaryKey = 'plan';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'jogo_id',
        'ram',
        'disk',
        'egg',
        'cpu',
        'location_id',
    ];

    protected $casts = [
        'ram' => 'integer',
        'disk' => 'integer',
        'cpu' => 'integer',
    ];

    public function pterodactylEgg()
    {
        return $this->belongsTo(PterodactylEgg::class, 'egg');
    }

    public function jogo()
    {
        return $this->belongsTo(JogoProd::class, 'jogo_id');
    }

    public function ramFormatada(): string
    {
        return $this->ram > 0 ? number_format($this->ram / 1000, 0, ',', '.') . ' GB' : 'Ilimitado';
    }

    public function discoFormatado(): string
    {
        return $this->disk > 0 ? number_format($this->disk / 1000, 0, ',', '.') . ' GB' : 'Ilimitado';
    }

    public function cpuFormatada(): string
    {
        return $this->cpu ? $this->cpu . ' vCPU' : 'Ilimitado';
    }

        public function precoFormatada(): string
    {
return $this->price
    ? 'R$ ' . number_format($this->price / 100, 2, ',', '.')
    : 'Gratuito';
    }
}
