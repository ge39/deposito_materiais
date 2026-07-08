<?php

namespace App\Services\Logistica;

use App\Models\Funcionario;
use App\Models\Romaneio;
use App\Models\Veiculo;
use Illuminate\Support\Collection;

class EquipeService
{
    public function listarMotoristasDisponiveis(): Collection
    {
        return Funcionario::query()
            ->where('ativo', 1)
            ->orderBy('nome')
            ->get();
    }

    public function listarVeiculosDisponiveis(): Collection
    {
        return Veiculo::query()
            ->where('ativo', 1)
            ->where('status', 'Ativo')
            ->where('disponibilidade', 'Disponivel')
            ->orderBy('placa')
            ->get();
    }

    public function listarVeiculosParaRomaneio(Romaneio $romaneio): Collection
    {
        return Veiculo::query()
            ->where('ativo', 1)
            ->where('status', 'Ativo')
            ->where(function ($query) use ($romaneio) {
                $query->where('disponibilidade', 'Disponivel');

                if (!empty($romaneio->veiculo_id)) {
                    $query->orWhere('id', $romaneio->veiculo_id);
                }
            })
            ->orderBy('placa')
            ->get();
    }
}