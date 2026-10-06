<?php

namespace App\Http\Controllers\BI;

use App\Http\Controllers\Controller;
use App\Services\BI\BiLogisticaService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BiLogisticaController extends Controller
{
    public function __construct(
        private readonly BiLogisticaService $service
    ) {
    }

    public function index(Request $request): View
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

        $dados = $request->validate([
            'data_inicio' => [
                'nullable',
                'date_format:Y-m-d',
            ],
            'data_fim' => [
                'nullable',
                'date_format:Y-m-d',
            ],
            'status' => [
                'nullable',
                'string',
                'max:80',
            ],
            'periodo' => [
                'nullable',
                'in:manha,tarde,comercial',
            ],
            'motorista_id' => [
                'nullable',
                'integer',
            ],
            'veiculo_id' => [
                'nullable',
                'integer',
            ],
        ]);

        return view(
            'bi.logistica.index',
            $this->service->dashboard($dados)
        );
    }
}