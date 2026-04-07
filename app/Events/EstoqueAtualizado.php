<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EstoqueAtualizado
{
    use Dispatchable, SerializesModels;

    public int $produtoId;

    public function __construct(int $produtoId)
    {
        $this->produtoId = $produtoId;
    }
}