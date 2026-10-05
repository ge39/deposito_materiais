<?php

namespace App\Http\Controllers\BI;

use App\Http\Controllers\Controller;
use App\Services\BI\BiExecutivoService;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class BiExecutivoController extends Controller
{
    public function __construct(
        private readonly BiExecutivoService $service
    ) {
    }

    public function index(Request $request): View
    {
        $this->autorizar();

        $dados = $request->validate([
            'data_inicio' => [
                'nullable',
                'date_format:Y-m-d',
            ],

            'data_fim' => [
                'nullable',
                'date_format:Y-m-d',
            ],
        ]);

        $fim = ! empty($dados['data_fim'] ?? null)
            ? CarbonImmutable::createFromFormat(
                'Y-m-d',
                $dados['data_fim']
            )->endOfDay()
            : CarbonImmutable::now()->endOfDay();

        $inicio = ! empty($dados['data_inicio'] ?? null)
            ? CarbonImmutable::createFromFormat(
                'Y-m-d',
                $dados['data_inicio']
            )->startOfDay()
            : $fim->startOfMonth()->startOfDay();

        if ($inicio->greaterThan($fim)) {
            throw ValidationException::withMessages([
                'data_inicio' =>
                    'A data inicial não pode ser maior que a data final.',
            ]);
        }

        $indicadores = $this->service->obter(
            $inicio,
            $fim
        );

        return view(
            'bi.executivo.index',
            [
                'inicio' => $inicio,
                'fim' => $fim,
                'indicadores' => $indicadores,
            ]
        );
    }

    private function autorizar(): void
    {
        abort_unless(
            auth()->check()
            && in_array(
                auth()->user()->nivel_acesso,
                [
                    'admin',
                    'gerente',
                ],
                true
            ),
            403
        );
    }
}