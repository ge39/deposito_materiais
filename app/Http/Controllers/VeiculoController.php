<?php

namespace App\Http\Controllers;

use App\Models\ClasseVeiculo;
use App\Models\Empresa;
use App\Models\Funcionario;
use App\Models\TipoCarroceria;
use App\Models\TipoVeiculo;
use App\Models\Veiculo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VeiculoController extends Controller
{
    public function index(Request $request)
    {
        $query = Veiculo::with([
            'tipoVeiculo',
            'classeVeiculo',
            'tipoCarroceria',
            'motoristaPadrao',
        ]);

        if ($request->filled('busca')) {
            $busca = trim($request->busca);

            $query->where(function ($q) use ($busca) {
                $q->where('placa', 'like', "%{$busca}%")
                    ->orWhere('modelo', 'like', "%{$busca}%")
                    ->orWhere('marca', 'like', "%{$busca}%")
                    ->orWhere('renavam', 'like', "%{$busca}%")
                    ->orWhere('chassi', 'like', "%{$busca}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('disponibilidade')) {
            $query->where(
                'disponibilidade',
                $request->disponibilidade
            );
        }

        if ($request->filled('tipo_frota')) {
            $query->where(
                'tipo_frota',
                $request->tipo_frota
            );
        }

        if ($request->filled('tipo_veiculo_id')) {
            $query->where(
                'tipo_veiculo_id',
                $request->tipo_veiculo_id
            );
        }

        if ($request->filled('classe_veiculo_id')) {
            $query->where(
                'classe_veiculo_id',
                $request->classe_veiculo_id
            );
        }

        if ($request->filled('tipo_carroceria_id')) {
            $query->where(
                'tipo_carroceria_id',
                $request->tipo_carroceria_id
            );
        }

        $veiculos = $query
            ->orderBy('placa')
            ->paginate(15)
            ->withQueryString();

        $kpis = [
            'total' => Veiculo::count(),

            'ativos' => Veiculo::where(
                'status',
                'Ativo'
            )->count(),

            'disponiveis' => Veiculo::where(
                'disponibilidade',
                'Disponível'
            )->count(),

            'em_operacao' => Veiculo::whereIn(
                'disponibilidade',
                [
                    'Reservado',
                    'Carregando',
                    'Em rota',
                ]
            )->count(),

            'manutencao' => Veiculo::where(function ($q) {
                $q->where(
                    'status',
                    'Manutenção'
                )
                    ->orWhere(
                        'disponibilidade',
                        'Manutenção'
                    );
            })->count(),
        ];

        return view(
            'veiculos.index',
            compact(
                'veiculos',
                'kpis'
            )
        );
    }

    public function create()
    {
        $veiculo = new Veiculo();

        $motoristas = Funcionario::where(
            'ativo',
            1
        )
            ->orderBy('nome')
            ->get();

        $tiposVeiculo = TipoVeiculo::where(
            'ativo',
            true
        )
            ->orderBy('descricao')
            ->get();

        return view(
            'veiculos.create',
            compact(
                'veiculo',
                'motoristas',
                'tiposVeiculo'
            )
        );
    }

    public function store(Request $request)
    {
        $dados = $this->validarDados(
            $request
        );

        $dados = $this->normalizarCheckboxes(
            $dados
        );

        $dados = $this->normalizarRastreamento(
            $dados
        );

        $empresa = Empresa::ativa();

        abort_unless(
            $empresa,
            422,
            'Empresa ativa não encontrada.'
        );

        $dados['empresa_id'] = $empresa->id;

        Veiculo::create($dados);

        return redirect()
            ->route('veiculos.index')
            ->with(
                'success',
                'Veículo cadastrado com sucesso.'
            );
    }

    public function edit(Veiculo $veiculo)
    {
        $motoristas = Funcionario::where(
            'ativo',
            1
        )
            ->orderBy('nome')
            ->get();

        $tiposVeiculo = TipoVeiculo::where(
            'ativo',
            true
        )
            ->orderBy('descricao')
            ->get();

        $classesVeiculo = ClasseVeiculo::where(
            'tipo_veiculo_id',
            $veiculo->tipo_veiculo_id
        )
            ->where(
                'ativo',
                true
            )
            ->orderBy('descricao')
            ->get();

        $tiposCarroceria = TipoCarroceria::select(
            'tipos_carroceria.*'
        )
            ->join(
                'classe_veiculo_carroceria',
                'classe_veiculo_carroceria.tipo_carroceria_id',
                '=',
                'tipos_carroceria.id'
            )
            ->where(
                'classe_veiculo_carroceria.classe_veiculo_id',
                $veiculo->classe_veiculo_id
            )
            ->where(
                'classe_veiculo_carroceria.ativo',
                true
            )
            ->orderBy('descricao')
            ->get();

        return view(
            'veiculos.edit',
            compact(
                'veiculo',
                'motoristas',
                'tiposVeiculo',
                'classesVeiculo',
                'tiposCarroceria'
            )
        );
    }

    public function update(
        Request $request,
        Veiculo $veiculo
    ) {
        $dados = $this->validarDados(
            $request,
            $veiculo->id
        );

        $dados = $this->normalizarCheckboxes(
            $dados
        );

        $dados = $this->normalizarRastreamento(
            $dados
        );

        $veiculo->update($dados);

        return redirect()
            ->route('veiculos.index')
            ->with(
                'success',
                'Veículo atualizado com sucesso.'
            );
    }

    public function destroy(Veiculo $veiculo)
    {
        $veiculo->delete();

        return redirect()
            ->route('veiculos.index')
            ->with(
                'success',
                'Veículo removido com sucesso.'
            );
    }

    private function buscarMotoristas()
    {
        return Funcionario::query()
            ->where(
                'ativo',
                true
            )
            ->orderBy('nome')
            ->get();
    }

    private function validarDados(
        Request $request,
        ?int $veiculoId = null
    ): array {
        return $request->validate([
            'placa' => [
                'required',
                'string',
                'max:20',
                'unique:veiculos,placa,' . $veiculoId,
            ],

            'marca' => [
                'nullable',
                'string',
                'max:80',
            ],

            'modelo' => [
                'nullable',
                'string',
                'max:100',
            ],

            'ano_fabricacao' => [
                'nullable',
                'integer',
                'min:1950',
                'max:' . (date('Y') + 1),
            ],

            'cor' => [
                'nullable',
                'string',
                'max:40',
            ],

            'chassi' => [
                'nullable',
                'string',
                'max:80',
            ],

            'renavam' => [
                'nullable',
                'string',
                'max:40',
            ],

            'tipo_veiculo_id' => [
                'required',
                'integer',
                'exists:tipos_veiculo,id',
            ],

            'classe_veiculo_id' => [
                'required',
                'integer',
                'exists:classes_veiculo,id',
            ],

            'tipo_carroceria_id' => [
                'required',
                'integer',
                'exists:tipos_carroceria,id',
            ],

            'tipo_frota' => [
                'required',
                'in:Frota,Agregado,Terceirizado',
            ],

            'motorista_padrao_id' => [
                'nullable',
                'exists:funcionarios,id',
            ],

            'categoria_cnh' => [
                'nullable',
                'string',
                'max:10',
            ],

            'capacidade_kg' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'capacidade_m3' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'capacidade_unidades' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'capacidade_paletes' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'comprimento_m' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'largura_m' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'altura_m' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'possui_munck' => [
                'nullable',
                'boolean',
            ],

            'possui_rastreador' => [
                'nullable',
                'boolean',
            ],

            'rastreamento_ativo' => [
                'nullable',
                'boolean',
            ],

            'traccar_unique_id' => [
                'nullable',
                'required_if:possui_rastreador,1',
                'string',
                'max:128',
                Rule::unique(
                    'veiculos',
                    'traccar_unique_id'
                )->ignore($veiculoId),
            ],

            'aceita_areia_pedra' => [
                'nullable',
                'boolean',
            ],

            'aceita_blocos_tijolos' => [
                'nullable',
                'boolean',
            ],

            'aceita_cimento_argamassa' => [
                'nullable',
                'boolean',
            ],

            'aceita_tintas_quimicos' => [
                'nullable',
                'boolean',
            ],

            'aceita_telhas' => [
                'nullable',
                'boolean',
            ],

            'aceita_madeiras' => [
                'nullable',
                'boolean',
            ],

            'restricao_rodizio' => [
                'nullable',
                'boolean',
            ],

            'restricao_zona_central' => [
                'nullable',
                'boolean',
            ],

            'restricao_altura' => [
                'nullable',
                'boolean',
            ],

            'restricao_peso' => [
                'nullable',
                'boolean',
            ],

            'status' => [
                'required',
                'in:Ativo,Inativo,Manutencao',
            ],

            'disponibilidade' => [
                'required',
                'in:Disponível,Reservado,Carregando,Em rota,Manutencao,Indisponível',
            ],

            'observacao' => [
                'nullable',
                'string',
            ],
        ]);
    }

    private function normalizarCheckboxes(
        array $dados
    ): array {
        $checkboxes = [
            'possui_munck',
            'possui_rastreador',
            'rastreamento_ativo',

            'aceita_areia_pedra',
            'aceita_blocos_tijolos',
            'aceita_cimento_argamassa',
            'aceita_tintas_quimicos',
            'aceita_telhas',
            'aceita_madeiras',

            'restricao_rodizio',
            'restricao_zona_central',
            'restricao_altura',
            'restricao_peso',
        ];

        foreach ($checkboxes as $campo) {
            $dados[$campo] =
                isset($dados[$campo])
                && (bool) $dados[$campo];
        }

        return $dados;
    }

    private function normalizarRastreamento(
        array $dados
    ): array {
        if (
            ! ($dados['possui_rastreador'] ?? false)
        ) {
            $dados['rastreamento_ativo'] = false;
            $dados['traccar_unique_id'] = null;

            return $dados;
        }

        $uniqueId = trim(
            (string) (
                $dados['traccar_unique_id']
                ?? ''
            )
        );

        $dados['traccar_unique_id'] =
            $uniqueId !== ''
                ? $uniqueId
                : null;

        return $dados;
    }

    public function show(Veiculo $veiculo)
    {
        $veiculo->load(
            'motoristaPadrao'
        );

        return view(
            'veiculos.show',
            compact('veiculo')
        );
    }
}
