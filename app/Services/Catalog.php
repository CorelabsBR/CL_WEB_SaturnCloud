<?php

namespace App\Services;

use Illuminate\Support\Collection;

class Catalog
{
    public function products(?string $type = null): Collection
    {
        $groups = $type ? [$type => config("catalog.{$type}", [])] : config('catalog', []);

        return collect($groups)->flatMap(function ($products, $type) {
            return collect($products)->map(fn ($product) => (object) array_merge([
                'descricao' => null, 'ram' => null, 'armazenamento' => null,
                'processamento' => null, 'bancos_dados' => null, 'dominios' => null,
                'ddos_protection' => false, 'featured' => false, 'imagem' => null,
            ], $product, ['type' => $type]));
        })->values();
    }

    public function find(string $id): object
    {
        return $this->products()->first(fn ($product) => (string) $product->id === $id) ?? abort(404);
    }
}
