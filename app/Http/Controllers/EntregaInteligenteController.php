<?php

namespace App\Http\Controllers;

use App\Services\Expedicao\EntregaInteligenteConsolidacaoService;
use App\Services\Expedicao\EntregaInteligenteService;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class EntregaInteligenteController extends Controller
{
    public function __construct(
        private readonly EntregaInteligenteService $entregaInteligenteService,
        private readonly EntregaInteligenteConsolidacaoService $consolidacaoService
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

        $dashboard = $this
            ->entregaInteligenteService
            ->montarDashboard($dataReferencia);

        $dashboard['paresConsolidacaoIgnorados'] = $this
            ->consolidacaoService
            ->paresIgnorados();

        return view(
            'entregas_inteligentes.index',
            $dashboard
        );
    }
}