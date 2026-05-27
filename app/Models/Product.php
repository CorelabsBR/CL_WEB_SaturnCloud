<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'products';

    const CREATED_AT = 'criado_em';
    const UPDATED_AT = 'atualizado_em';

    protected $fillable = [
        'nome',
        'descricao',
        'imagem',
        'preco',
        'categoria_id',
        'jogo_id',
        'plan',
        'local_id',
        'ddos_protection',
        'featured',
        'sort_order',
        'ram',
        'armazenamento',
        'processamento',
        'bancos_dados',
        'dominios',
    ];

    protected $casts = [
        'preco' => 'decimal:2',
        'ddos_protection' => 'boolean',
        'featured' => 'boolean',
    ];

    public function categoria()
    {
        return $this->belongsTo(CategoriaProd::class, 'categoria_id');
    }

    public function jogo()
    {
        return $this->belongsTo(JogoProd::class, 'jogo_id');
    }

    public function planModel()
    {
        return $this->belongsTo(Plan::class, 'plan', 'plan');
    }
}
