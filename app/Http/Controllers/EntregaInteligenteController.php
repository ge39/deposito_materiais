<?php

namespace App\Http\Controllers;

use App\Services\Expedicao\EntregaInteligenteService;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class EntregaInteligenteController extends Controller
{
    public function __construct(
        private readonly EntregaInteligenteService $entregaInteligenteService
    ) {
    }

    public function index(Request $request): View
    {
        $dados = $request->validate([
            'data_referencia' => [
                'nullable',
                'date_format:Y-m-d',
            ],
        ]);

        $dataReferencia = ! empty($dados['data_referencia'])
            ? CarbonImmutable::parse(
                $dados['data_referencia']
            )->startOfDay()
            : CarbonImmutable::today();

        return view(
            'entregas_inteligentes.index',
            $this
                ->entregaInteligenteService
                ->montarDashboard($dataReferencia)
        );
    }
}