<?php

namespace App\Http\Controllers;

use App\Services\Expedicao\EntregaInteligenteConsolidacaoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EntregaInteligenteConsolidacaoController extends Controller
{
    public function __construct(
        private readonly EntregaInteligenteConsolidacaoService $service
    ) {
    }

    public function decidir(Request $request): JsonResponse
    {
        $dados = $request->validate([
            'entrega_origem_id' => [
                'required',
                'integer',
                'exists:entregas,id',
            ],
            'entrega_destino_id' => [
                'required',
                'integer',
                'different:entrega_origem_id',
                'exists:entregas,id',
            ],
            'decisao' => [
                'required',
                Rule::in([
                    'consolidar',
                    'manter_separado',
                ]),
            ],
            'distancia_km' => [
                'nullable',
                'numeric',
                'min:0',
            ],
            'disponibilidade_confirmada' => [
                'nullable',
                'boolean',
            ],
        ]);

        $resultado = $this->service->decidir(
            $dados,
            (int) $request->user()->id
        );

        return response()->json($resultado);
    }
}