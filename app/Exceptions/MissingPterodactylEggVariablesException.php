<?php

namespace App\Exceptions;

use RuntimeException;

class MissingPterodactylEggVariablesException extends RuntimeException
{
    public function __construct(private readonly array $variables)
    {
        parent::__construct('Variáveis obrigatórias da egg sem valor: ' . implode(', ', $variables));
    }

    public function variables(): array
    {
        return $this->variables;
    }
}
