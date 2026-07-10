<?php

namespace App\Http\Controllers;

use App\Models\ClasseVeiculo;
use Illuminate\Http\JsonResponse;

class FrotaController extends Controller
{
    /**
     * Retorna as classes pertencentes ao tipo de veículo.
     */
    public function classesPorTipo(int $tipoVeiculoId): JsonResponse
    {
        $classes = ClasseVeiculo::where('tipo_veiculo_id', $tipoVeiculoId)
            ->where('ativo', true)
            ->orderBy('descricao')
            ->get([
                'id',
                'descricao',
            ]);

        return response()->json($classes);
    }

    /**
     * Retorna as carrocerias compatíveis com a classe do veículo.
     */
    public function carroceriasPorClasse(int $classeVeiculoId): JsonResponse
    {
        $classe = ClasseVeiculo::with([
            'carrocerias' => function ($query) {

                $query->where('tipos_carroceria.ativo', true)
                    ->wherePivot('ativo', true)
                    ->orderBy('tipos_carroceria.descricao');

            }
        ])->findOrFail($classeVeiculoId);

        return response()->json(
            $classe->carrocerias->map(function ($carroceria) {
                return [
                    'id' => $carroceria->id,
                    'descricao' => $carroceria->descricao,
                ];
            })
        );
    }
}