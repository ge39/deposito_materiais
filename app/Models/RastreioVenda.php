<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Venda;
use App\Models\ItensVenda;

class RastreioVenda extends Model
{
    use HasFactory;

    // Aqui podemos definir métodos para combinar filtros
    public static function buscar(array $filtros)
    {
        $query = ItensVenda::query()->with(['venda.cliente', 'produto', 'venda']);

        if (!empty($filtros['cliente_id'])) {
            $query->whereHas('venda', function($q) use ($filtros) {
                $q->where('cliente_id', $filtros['cliente_id']);
            });
        }

        if (!empty($filtros['produto_id'])) {
            $query->where('produto_id', $filtros['produto_id']);
        }

        if (!empty($filtros['lote_id'])) {
            $query->where('lote_id', $filtros['lote_id']);
        }

        if (!empty($filtros['venda_id'])) {
            $query->where('venda_id', $filtros['venda_id']);
        }

        if (!empty($filtros['data_venda'])) {
            $query->whereHas('venda', function($q) use ($filtros) {
                $q->whereDate('created_at', $filtros['data_venda']);
            });
        }

        return $query->get();
    }
}
