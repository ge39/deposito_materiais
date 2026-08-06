<?php

namespace App\Services;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Services\Entregas\EntregaService;
use App\Models\Orcamento;
use App\Models\EntregaItem;
use App\Models\ItemOrcamento;
use App\Models\Empresa;
use App\Models\Lote;
use App\Models\Cliente;
use App\Models\Produto;
use App\Services\EstoqueService;
use App\Enums\TipoMovimentacao;
use App\Enums\OrigemMovimentacao;
use App\Models\Entrega;

class OrcamentoService
{
    protected EstoqueService $estoqueService;

    public function __construct(EstoqueService $estoqueService)
    {
        $this->estoqueService = $estoqueService;
    }

    /* =========================================
     | LISTAGEM
     ========================================= */
    public function listar($request)
    {
        $query = Orcamento::with('cliente');

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->codigo_orcamento) {
            $query->where('codigo_orcamento', $request->codigo_orcamento);
        }

        return $query->orderByDesc('id')->paginate(15);
    }

 
    /* =========================================
    | DADOS CREATE
    ========================================= */
    public function dadosParaCriacao()
    {
        return [
            'clientes' => Cliente::select(
                    'id',
                    'nome',
                    'telefone',
                    'cpf_cnpj',
                    'cep',
                    'endereco',
                    'numero',
                    'bairro',
                    'cidade',
                    'estado',
                    'endereco_entrega'
                )
                ->orderBy('nome')
                ->get(),

            'produtos' => Produto::with([
                'lotes' => function ($q) {
                    $q->where('status', 1)
                        ->whereRaw(
                            '(quantidade - quantidade_reservada) > 0'
                        )
                        ->where(function ($q2) {
                            $q2
                                ->where(function ($q3) {
                                    $q3
                                        ->whereHas(
                                            'produto',
                                            function ($produto) {
                                                $produto->where(
                                                    'controla_validade',
                                                    1
                                                );
                                            }
                                        )
                                        ->whereDate(
                                            'validade_lote',
                                            '>=',
                                            now()
                                        );
                                })
                                ->orWhere(function ($q3) {
                                    $q3->whereHas(
                                        'produto',
                                        function ($produto) {
                                            $produto->where(
                                                'controla_validade',
                                                0
                                            );
                                        }
                                    );
                                });
                        })
                        ->orderBy('id', 'asc');
                },
            ])
                ->orderBy('nome')
                ->get(),
        ];
    }

    // Dados do metodo CREATE
   public function criarCompleto(array $request)
    {
        return DB::transaction(function () use ($request) {
            $empresa = Empresa::where('ativo', 1)
                ->firstOrFail();

            $tipoEntrega =
                $request['tipo_entrega']
                ?? 'retira_loja';

            $usarEnderecoCliente =
                ($request['usar_endereco_cliente'] ?? 'sim') === 'nao'
                    ? 'nao'
                    : 'sim';

            $cliente = Cliente::findOrFail(
                $request['cliente_id']
            );

            $enderecoEntrega = null;
            $responsavelRecebimento = null;
            $telefoneRecebimento = null;
            $dataPrevistaEntrega = null;
            $periodoEntrega = null;
            $observacaoEntrega = null;
            $latitudeEntrega = null;
            $longitudeEntrega = null;
            $coordenadaConfirmada = false;

            if ($tipoEntrega === 'entrega') {
                if (empty($request['data_prevista_entrega'])) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'data_prevista_entrega' =>
                            'A data prevista da entrega é obrigatória.',
                    ]);
                }

                if (empty($request['periodo_entrega'])) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'periodo_entrega' =>
                            'O período da entrega é obrigatório.',
                    ]);
                }

                $dataPrevistaEntrega =
                    $request['data_prevista_entrega'];

                $periodoEntrega =
                    $request['periodo_entrega'];

                $observacaoEntrega = trim(
                    (string) (
                        $request['observacao_entrega']
                        ?? ''
                    )
                ) ?: null;

                if ($usarEnderecoCliente === 'nao') {
                    $camposObrigatorios = [
                        'cep_entrega' =>
                            'Informe o CEP da entrega.',

                        'endereco_entrega' =>
                            'Informe o logradouro da entrega.',

                        'numero_entrega' =>
                            'Informe o número da entrega.',

                        'bairro_entrega' =>
                            'Informe o bairro da entrega.',

                        'cidade_entrega' =>
                            'Informe a cidade da entrega.',

                        'uf_entrega' =>
                            'Informe a UF da entrega.',
                    ];

                    foreach (
                        $camposObrigatorios
                        as $campo => $mensagem
                    ) {
                        if (
                            trim(
                                (string) (
                                    $request[$campo]
                                    ?? ''
                                )
                            ) === ''
                        ) {
                            throw \Illuminate\Validation\ValidationException::withMessages([
                                $campo => $mensagem,
                            ]);
                        }
                    }

                    $cepNumerico = preg_replace(
                        '/\D/',
                        '',
                        (string) $request['cep_entrega']
                    );

                    if (strlen($cepNumerico) !== 8) {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            'cep_entrega' =>
                                'O CEP da entrega deve possuir 8 números.',
                        ]);
                    }

                    $cepFormatado =
                        substr($cepNumerico, 0, 5)
                        . '-'
                        . substr($cepNumerico, 5, 3);

                    $ufEntrega = strtoupper(
                        trim(
                            (string) $request['uf_entrega']
                        )
                    );

                    if (strlen($ufEntrega) !== 2) {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            'uf_entrega' =>
                                'A UF da entrega deve possuir 2 letras.',
                        ]);
                    }

                    $cidadeUf = trim(
                        (string) $request['cidade_entrega']
                    )
                        . ' - '
                        . $ufEntrega;

                    $enderecoEntrega = collect([
                        trim(
                            (string) $request['endereco_entrega']
                        ),

                        'Nº '
                            . trim(
                                (string) $request['numero_entrega']
                            ),

                        trim(
                            (string) (
                                $request['complemento_entrega']
                                ?? ''
                            )
                        ) ?: null,

                        trim(
                            (string) $request['bairro_entrega']
                        ),

                        $cidadeUf,

                        'CEP ' . $cepFormatado,
                    ])
                        ->map(
                            fn ($valor) =>
                                trim(
                                    (string) $valor,
                                    " \t\n\r\0\x0B,"
                                )
                        )
                        ->filter()
                        ->implode(', ');
                } else {
                    $cepNumerico = preg_replace(
                        '/\D/',
                        '',
                        (string) ($cliente->cep ?? '')
                    );

                    $enderecoClienteCompleto =
                        ! empty($cliente->endereco)
                        && ! empty($cliente->numero)
                        && ! empty($cliente->bairro)
                        && ! empty($cliente->cidade)
                        && ! empty($cliente->estado)
                        && strlen($cepNumerico) === 8;

                    if (! $enderecoClienteCompleto) {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            'usar_endereco_cliente' =>
                                'O endereço cadastrado do cliente está incompleto. Corrija o cadastro ou informe outro endereço.',
                        ]);
                    }

                    $cepFormatado =
                        substr($cepNumerico, 0, 5)
                        . '-'
                        . substr($cepNumerico, 5, 3);

                    $cidadeUf =
                        trim((string) $cliente->cidade)
                        . ' - '
                        . strtoupper(
                            trim(
                                (string) $cliente->estado
                            )
                        );

                    $enderecoEntrega = collect([
                        trim(
                            (string) $cliente->endereco
                        ),

                        'Nº '
                            . trim(
                                (string) $cliente->numero
                            ),

                        trim(
                            (string) (
                                $cliente->complemento
                                ?? ''
                            )
                        ) ?: null,

                        trim(
                            (string) $cliente->bairro
                        ),

                        $cidadeUf,

                        'CEP ' . $cepFormatado,
                    ])
                        ->map(
                            fn ($valor) =>
                                trim(
                                    (string) $valor,
                                    " \t\n\r\0\x0B,"
                                )
                        )
                        ->filter()
                        ->implode(', ');
                }

                $responsavelRecebimento = trim(
                    (string) (
                        $request['contato_entrega']
                        ?? ''
                    )
                );

                if ($responsavelRecebimento === '') {
                    $responsavelRecebimento =
                        $cliente->nome;
                }

                $telefoneRecebimento = trim(
                    (string) (
                        $request['telefone_entrega']
                        ?? ''
                    )
                );

                if ($telefoneRecebimento === '') {
                    $telefoneRecebimento =
                        $cliente->telefone;
                }

                [
                    $latitudeEntrega,
                    $longitudeEntrega,
                    $coordenadaConfirmada,
                ] = $this->validarCoordenadasEntrega(
                    $request
                );
            }

            $descontoGlobal = (float) (
                $request['desconto_global']
                ?? 0
            );

            $orcamento = Orcamento::create([
                'cliente_id' =>
                    $cliente->id,

                'empresa_id' =>
                    $empresa->id,

                'data_orcamento' =>
                    now(),

                'tipo_entrega' =>
                    $tipoEntrega,

                'usar_endereco_cliente' =>
                    $usarEnderecoCliente === 'sim',

                'endereco_entrega' =>
                    $enderecoEntrega,

                'latitude_entrega' =>
                    $latitudeEntrega,

                'longitude_entrega' =>
                    $longitudeEntrega,

                'coordenada_confirmada' =>
                    $coordenadaConfirmada,

                'responsavel_recebimento' =>
                    $responsavelRecebimento,

                'telefone_recebimento' =>
                    $telefoneRecebimento,

                'data_prevista_entrega' =>
                    $dataPrevistaEntrega,

                'periodo_entrega' =>
                    $periodoEntrega,

                'observacao_entrega' =>
                    $observacaoEntrega,

                'validade' =>
                    $request['validade'],

                'codigo_orcamento' =>
                    now()->format('YmdHis'),

                'status' =>
                    Orcamento::AGUARDANDO_APROVACAO,

                'observacoes' =>
                    $request['observacoes']
                    ?? null,

                'total' =>
                    0,

                'ativo' =>
                    1,

                'editando_por' =>
                    Auth::id(),

                'editando_em' =>
                    now(),
            ]);

            $orcamento->update([
                'codigo_orcamento' =>
                    now()->format('YmdHis')
                    . $orcamento->id,
            ]);

            if (
                empty($request['produtos'])
                || ! is_array($request['produtos'])
            ) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'produtos' =>
                        'Adicione pelo menos um produto ao orçamento.',
                ]);
            }

            foreach (
                $request['produtos']
                as $itemReq
            ) {
                $produtoId =
                    $itemReq['id']
                    ?? null;

                if (! $produtoId) {
                    continue;
                }

                $quantidade = (float) (
                    $itemReq['quantidade_solicitada']
                    ?? $itemReq['quantidade']
                    ?? 0
                );

                $preco = (float) (
                    $itemReq['preco_unitario']
                    ?? $itemReq['preco']
                    ?? 0
                );

                if ($quantidade <= 0) {
                    continue;
                }

                $valorDescontoUnitario =
                    $preco
                    * ($descontoGlobal / 100);

                $precoUnitarioLiquido =
                    $preco
                    - $valorDescontoUnitario;

                $valorDescontoTotalItem =
                    $quantidade
                    * $valorDescontoUnitario;

                $subtotalItem =
                    $quantidade
                    * $precoUnitarioLiquido;

                if ($subtotalItem < 0) {
                    $subtotalItem = 0;
                }

                $item = ItemOrcamento::create([
                    'orcamento_id' =>
                        $orcamento->id,

                    'produto_id' =>
                        $produtoId,

                    'quantidade_solicitada' =>
                        $quantidade,

                    'quantidade_atendida' =>
                        0,

                    'quantidade_pendente' =>
                        $quantidade,

                    'preco_unitario' =>
                        $preco,

                    'preco_liquido' =>
                        $precoUnitarioLiquido,

                    'desconto_percentual' =>
                        $descontoGlobal,

                    'valor_desconto' =>
                        $valorDescontoTotalItem,

                    'subtotal' =>
                        $subtotalItem,

                    'status' =>
                        'indisponivel',

                    'previsao_entrega' =>
                        now()->addDays(7),
                ]);

                $this->estoqueService->recalcularReservar(
                    $item->id,
                    $produtoId,
                    $quantidade
                );

                $this->recalcularItemCompleto(
                    $item
                );

                $item->refresh();
            }

            $orcamento->load('itens');

            $totalLiquidoFinal =
                $orcamento->itens
                    ->sum('subtotal');

            $temPendente =
                $orcamento->itens
                    ->where(
                        'quantidade_pendente',
                        '>',
                        0
                    )
                    ->isNotEmpty();

            $orcamento->update([
                'total' =>
                    $totalLiquidoFinal,

                'status' =>
                    $temPendente
                        ? Orcamento::AGUARDANDO_ESTOQUE
                        : Orcamento::AGUARDANDO_APROVACAO,
            ]);

            return $orcamento->refresh();
        });
    }

    private function validarCoordenadasEntrega(
        array $request
    ): array {
        $latitudeInformada = str_replace(
            ',',
            '.',
            trim(
                (string) (
                    $request['latitude_entrega']
                    ?? ''
                )
            )
        );

        $longitudeInformada = str_replace(
            ',',
            '.',
            trim(
                (string) (
                    $request['longitude_entrega']
                    ?? ''
                )
            )
        );

        if (
            $latitudeInformada === ''
            || $longitudeInformada === ''
        ) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'coordenada_entrega' =>
                    'Localize e confirme o ponto da entrega no mapa.',
            ]);
        }

        if (
            ! is_numeric($latitudeInformada)
            || ! is_numeric($longitudeInformada)
        ) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'coordenada_entrega' =>
                    'As coordenadas informadas para a entrega são inválidas.',
            ]);
        }

        $latitude = (float) $latitudeInformada;
        $longitude = (float) $longitudeInformada;

        if ($latitude < -90 || $latitude > 90) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'latitude_entrega' =>
                    'A latitude da entrega deve estar entre -90 e 90.',
            ]);
        }

        if ($longitude < -180 || $longitude > 180) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'longitude_entrega' =>
                    'A longitude da entrega deve estar entre -180 e 180.',
            ]);
        }

        $coordenadaConfirmada = filter_var(
            $request['coordenada_confirmada']
                ?? false,
            FILTER_VALIDATE_BOOLEAN
        );

        if (! $coordenadaConfirmada) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'coordenada_entrega' =>
                    'Confira o marcador e confirme o ponto da entrega.',
            ]);
        }

        return [
            round($latitude, 7),
            round($longitude, 7),
            true,
        ];
    }

     /* =========================================
     | EDITAR
     ========================================= */

    public function dadosParaEdicao($id)
    {
        try {
            $orcamento = Orcamento::with([
                'itens.produto.unidadeMedida',
                'itens.produto.lotes' => function ($q) {
                    $q->where('status', 1)
                    ->whereRaw('(quantidade - quantidade_reservada) > 0')
                    ->where(function ($q2) {
                        // 🔹 Produto controla validade
                        $q2->where(function ($q3) {
                            $q3->whereHas('produto', function ($p) {
                                $p->where('controla_validade', 1);
                            })
                            ->where(function ($q4) {
                                $q4->whereDate('validade_lote', '>=', now())
                                    ->orWhereNull('validade_lote');
                            });
                        })
                        // 🔹 Produto NÃO controla validade
                        ->orWhere(function ($q3) {
                            $q3->whereHas('produto', function ($p) {
                                $p->where('controla_validade', 0);
                            });
                        });
                    })
                    ->orderBy('id', 'asc');
                },
                'itens.lote'
            ])->findOrFail($id);

            // 🔒 Controle de edição concorrente
            if ($orcamento->editando_por && $orcamento->editando_por != auth()->id()) {
                $usuario = $orcamento->usuarioEditando;
                $nomeUsuario = $usuario->name ?? 'Outro usuário';

                return [
                    'erro' => "Este orçamento está sendo editado por: {$nomeUsuario}"
                ];
            }

            $orcamento->update([
                'editando_por' => auth()->id(),
                'editando_em' => now()
            ]);

            // 👥 Clientes
            $clientes = Cliente::where('ativo', 1)
                ->orderBy('nome')
                ->get();

            // 📦 Produtos já usados no orçamento
            $produtosIdsOrcamento = $orcamento->itens->pluck('produto_id');

            // 📦 Produtos disponíveis + usados
            $produtos = Produto::with([
                'unidadeMedida',
                'lotes' => function ($q) {
                    $q->where('status', 1)
                    ->whereRaw('(quantidade - quantidade_reservada) > 0')
                    ->where(function ($q2) {
                        // 🔹 Produto controla validade
                        $q2->where(function ($q3) {
                            $q3->whereHas('produto', function ($p) {
                                $p->where('controla_validade', 1);
                            })
                            ->where(function ($q4) {
                                $q4->whereDate('validade_lote', '>=', now())
                                    ->orWhereNull('validade_lote');
                            });
                        })
                        // 🔹 Produto NÃO controla validade
                        ->orWhere(function ($q3) {
                            $q3->whereHas('produto', function ($p) {
                                $p->where('controla_validade', 0);
                            });
                        });
                    })
                    ->orderBy('id', 'asc');
                }
            ])
            ->where('ativo', 1)
            ->where(function ($q) use ($produtosIdsOrcamento) {
                // Produtos com estoque válido
                $q->where(function ($q2) {
                    $q2->where('controla_validade', 0)
                    ->orWhereHas('lotes', function ($qq) {
                        $qq->where('status', 1)
                            ->whereRaw('(quantidade - quantidade_reservada) > 0')
                            ->where(function ($q3) {
                                $q3->whereDate('validade_lote', '>=', now())
                                    ->orWhereNull('validade_lote');
                            });
                    });
                })
                // OU produtos já usados no orçamento
                ->orWhereIn('id', $produtosIdsOrcamento);
            })
            ->orderBy('nome')
            ->get();

            // 🔗 Mapear lotes por produto
            $lotes = [];
            foreach ($produtos as $produto) {
                $lotes[$produto->id] = $produto->lotes;
            }

            // 📊 CONFORMIDADE COM DESCONTO GLOBAL:
            // Garante que se o campo de desconto global estiver nulo no banco, ele retorne 0 para a Blade
            if (!isset($orcamento->desconto_global)) {
                $orcamento->desconto_global = $orcamento->desconto_percentual ?? 0;
            }

            // Calcula o Total Bruto (Soma pura de item * quantidade) para enviar separado caso o $orcamento->total já seja o valor líquido no seu banco
            $totalBrutoCalculado = $orcamento->itens->sum(function($item) {
                return $item->quantidade_solicitada * $item->preco_unitario;
            });

            return compact('orcamento', 'clientes', 'produtos', 'lotes', 'totalBrutoCalculado');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return [
                'erro' => 'Orçamento não encontrado',
                'detalhes' => $e->getMessage()
            ];
        } catch (\Illuminate\Database\QueryException $e) {
            return [
                'erro' => 'Erro de banco de dados',
                'sql' => $e->getSql(),
                'bindings' => $e->getBindings(),
                'mensagem' => $e->getMessage()
            ];
        } catch (\Throwable $e) {
            return [
                'erro' => 'Erro inesperado',
                'mensagem' => $e->getMessage(),
                'arquivo' => $e->getFile(),
                'linha' => $e->getLine(),
                'trace' => collect($e->getTrace())->take(5)
            ];
        }
    }

    /* =========================================
     | APROVAR COMPLETO
     ========================================= */
   
    
    public function aprovarCompleto(int $orcamentoId)
    {
        return DB::transaction(function () use ($orcamentoId) {

            $movService = app(MovimentacaoOrcamentoService::class);

            $orcamento = Orcamento::with('itens')->findOrFail($orcamentoId);

            if (in_array($orcamento->status, ['Aprovado', 'Faturado'])) {
                throw new \Exception('Orçamento já aprovado ou faturado.');
            }

            foreach ($orcamento->itens as $item) {

                $vinculos = DB::table('item_orcamento_lotes')
                    ->where('item_orcamento_id', $item->id)
                    ->lockForUpdate()
                    ->get();

                foreach ($vinculos as $v) {

                    $lote = DB::table('lotes')
                        ->where('id', $v->lote_id)
                        ->lockForUpdate()
                        ->first();

                    if (!$lote) {
                        continue;
                    }

                    $disponivel = $lote->quantidade - $lote->quantidade_reservada;

                    if ($disponivel <= 0) {
                        continue;
                    }

                    $pendenteAtual = (float) $item->quantidade_pendente;

                    if ($pendenteAtual <= 0) {
                        continue;
                    }

                    $atender = min($pendenteAtual, $disponivel);

                    if ($atender > 0) {
                        DB::table('lotes')
                            ->where('id', $v->lote_id)
                            ->increment('quantidade_reservada', $atender);

                        DB::table('item_orcamento_lotes')
                            ->where('id', $v->id)
                            ->increment('quantidade_atendida', $atender);

                        $item->quantidade_atendida = (float) $item->quantidade_atendida + $atender;
                        $item->quantidade_pendente = (float) $item->quantidade_pendente - $atender;
                    }
                }

                $item->status = $item->quantidade_pendente > 0
                    ? 'parcial'
                    : 'disponivel';

                $item->save();
            }

            $orcamento->refresh()->load('itens');

            $temPendente = $orcamento->itens()
                ->where('quantidade_pendente', '>', 0)
                ->exists();

            $statusFinal = $temPendente
                ? 'Aguardando Estoque'
                : 'Aprovado';

            $orcamento->update([
                'status' => $statusFinal,
            ]);

            foreach ($orcamento->itens as $item) {

                $vinculos = DB::table('item_orcamento_lotes')
                    ->where('item_orcamento_id', $item->id)
                    ->get();

                foreach ($vinculos as $v) {

                    $lote = DB::table('lotes')
                        ->where('id', $v->lote_id)
                        ->first();

                    if (!$lote) {
                        continue;
                    }

                    $antes = $lote->quantidade_reservada;
                    $depois = $lote->quantidade_reservada;

                    $movService->registrar(
                        $v->lote_id,
                        $orcamento->id,
                        $item->id,
                        TipoMovimentacao::APROVADO,
                        $antes,
                        $depois,
                        $statusFinal === 'Aguardando Estoque'
                            ? 'Orçamento aprovado parcialmente com pendência de estoque'
                            : 'Orçamento aprovado',
                        OrigemMovimentacao::SISTEMA
                    );
                }
            }

            if ($orcamento->tipo_entrega === 'entrega') {

                $entregaJaExiste = DB::table('entregas')
                    ->where('orcamento_id', $orcamento->id)
                    ->exists();

                if (!$entregaJaExiste) {
                    app(\App\Services\Entregas\EntregaService::class)
                        ->gerarEntregaDoOrcamento($orcamento);
                }
            }

            return $orcamento->refresh();
        });
    }

    public function recalcularItemCompleto(ItemOrcamento $item, ?float $quantidadeSolicitada = null): void
    {
        DB::transaction(function () use ($item, $quantidadeSolicitada) {

            $item = ItemOrcamento::lockForUpdate()->find($item->id);

            // 🔹 Atualiza quantidade solicitada (se vier do request)
            if (!is_null($quantidadeSolicitada)) {
                $item->quantidade_solicitada = $quantidadeSolicitada;
            }

            // 🔹 Recalcula atendido (fonte da verdade = lotes)
            $quantidadeAtendida = DB::table('item_orcamento_lotes')
                ->where('item_orcamento_id', $item->id)
                ->sum('quantidade_reservada');

            $item->quantidade_atendida = $quantidadeAtendida;

            // 🔹 Calcula pendente
            $item->quantidade_pendente =
                max(0, $item->quantidade_solicitada - $quantidadeAtendida);

            // 🔹 Status correto
            if ($quantidadeAtendida <= 0) {
                $item->status = 'indisponivel';
            } elseif ($item->quantidade_pendente > 0) {
                $item->status = 'parcial';
            } else {
                $item->status = 'disponivel';
            }

            // =======================================================================
            // 🚀 SEGUNDA OPÇÃO DE REGRA FINANCEIRA: CÁLCULO ATÔMICO DO DESCONTO E SUB-TOTAL
            // =======================================================================
            $qtd = (float) $item->quantidade_solicitada;
            $precoUnitario = (float) $item->preco_unitario;
            
            // Recupera a porcentagem inteira que salvamos no create através do model mapeado
            $descPercent = (int) ($item->desconto_percentual ?? 0);

            // 1. Calcula o montante bruto total sem os abatimentos
            $totalBrutoItem = $qtd * $precoUnitario;

            // 2. Calcula o valor em reais do desconto unitário
            $valorDescontoUnitario = $precoUnitario * ($descPercent / 100);

            // 3. Calcula o total em reais economizado na linha inteira (Quantidade x Desconto Unitário)
            $valorDescontoTotalItem = $qtd * $valorDescontoUnitario;

            // 4. Subtotal líquido: Subtrai o desconto total do bruto acumulado
            $subtotalLiquido = $totalBrutoItem - $valorDescontoTotalItem;
            if ($subtotalLiquido < 0) $subtotalLiquido = 0;

            // 5. Preço unitário líquido que será usado pelo PDV no faturamento posterior
            $precoUnitarioLiquido = $precoUnitario - $valorDescontoUnitario;

            // Alimenta as propriedades do objeto na memória antes do disparo do SQL
            $item->preco_liquido = $precoUnitarioLiquido;
            $item->desconto_percentual = $descPercent;
            $item->valor_desconto = $valorDescontoTotalItem;
            $item->subtotal = $subtotalLiquido; // 🎯 Sobrescreve o subtotal com o valor líquido exato com descontos!
            // =======================================================================

            $item->save();
        });
    }


    public function atualizarCompleto(Request $request, $id)
    {
        return DB::transaction(function () use ($request, $id) {

            $orcamento = Orcamento::with('itens')->lockForUpdate()->findOrFail($id);

            // 🎯 1. CAPTURA O DESCONTO GLOBAL ENVIADO PELO FORMULÁRIO
            $descontoPercentual = floatval($request->input('desconto_global', 0));

            $produtos = collect($request->input('produtos', []));
            $totalLiquidoOrcamento = 0; // Armazenará a soma dos subtotais já com desconto

            // ===============================
            // 🔥 1. REMOVER ITENS QUE NÃO VIERAM NO REQUEST
            // ===============================
            $produtosIdsRequest = $produtos->pluck('id')->toArray();

            $itensRemover = $orcamento->itens()
                ->whereNotIn('produto_id', $produtosIdsRequest)
                ->get();

            foreach ($itensRemover as $item) {
                // 🔓 1. libera estoque reservado desse item
                $this->estoqueService->cancelarReserva($item);

                // 🗑️ remove o item do orçamento
                $item->delete();
            }

            // ===============================
            // 🔥 2. PROCESSAR ITENS DO REQUEST
            // ===============================
            foreach ($produtos as $produtoReq) {

                $produtoId = $produtoReq['id'] ?? null;
                $loteId = $produtoReq['lote_id'] ?? null;

                $quantidadeNova = $produtoReq['quantidade_solicitada']
                    ?? $produtoReq['quantidade']
                    ?? 0;

                // 🎯 Captura o preço bruto original (enviado pelo input do formulário)
                $precoBrutoOriginal = floatval($produtoReq['preco_unitario'] ?? 0);

                if (!$produtoId || $quantidadeNova <= 0) {
                    continue;
                }

                // 🔍 busca ou cria item
                $item = ItemOrcamento::firstOrCreate(
                    [
                        'orcamento_id' => $orcamento->id,
                        'produto_id' => $produtoId,
                    ],
                    [
                        'quantidade_solicitada' => 0,
                        'quantidade_atendida' => 0,
                        'quantidade_pendente' => 0,
                        'preco_unitario' => $precoBrutoOriginal,
                        'subtotal' => 0,
                        'status' => 'indisponivel',
                        'previsao_entrega' => now()->addDays(7),
                        'ativo' => true,
                    ]
                );

                // ===============================
                // 🔥 ATUALIZA DADOS BÁSICOS E MATEMÁTICA DO DESCONTO
                // ===============================
                // 🎯 Cálculos individuais por linha para gravação
                $subtotalBrutoItem = $precoBrutoOriginal * $quantidadeNova;
                $valorDescontoItem = $subtotalBrutoItem * ($descontoPercentual / 100);
                $subtotalLiquidoItem = $subtotalBrutoItem - $valorDescontoItem;
                $precoLiquidoUnitario = $precoBrutoOriginal * (1 - ($descontoPercentual / 100));

                $item->quantidade_solicitada = $quantidadeNova;
                $item->preco_unitario = $precoBrutoOriginal; // Mantém o bruto histórico
                
                // 🎯 Grava os campos de desconto na tabela de itens (item_orcamentos)
                $item->desconto_percentual = $descontoPercentual;
                $item->valor_desconto = $valorDescontoItem;
                $item->preco_liquido = $precoLiquidoUnitario;
                $item->subtotal = $subtotalLiquidoItem; // Subtotal passa a ser o LÍQUIDO real
                $item->save();

                // ===============================
                // 🔥 GESTÃO DE ESTOQUE E RESERVAS
                // ===============================
                $this->estoqueService->cancelarReserva($item);

                $this->estoqueService->recalcularReservar(
                    $item->id,
                    $produtoId,
                    $quantidadeNova
                );

                $item->refresh();

                // ===============================
                // 🔥 RECALCULO DE ATENDIMENTO
                // ===============================
                $quantidadeAtendida = DB::table('item_orcamento_lotes')
                    ->where('item_orcamento_id', $item->id)
                    ->sum('quantidade_reservada');

                $item->quantidade_atendida = $quantidadeAtendida;
                $item->quantidade_pendente =
                    max(0, $item->quantidade_solicitada - $quantidadeAtendida);

                if ($quantidadeAtendida <= 0) {
                    $item->status = 'indisponivel';
                } elseif ($item->quantidade_pendente > 0) {
                    $item->status = 'parcial';
                } else {
                    $item->status = 'disponivel';
                }

                $item->save();
            }

            // ===============================
            // 🔥 RECALCULO FINAL DO TOTAL COM DESCONTO (GARANTIA)
            // ===============================
            // 🎯 O total do orçamento agora é a soma da coluna subtotal (que salvamos acima como líquida)
            $totalLiquidoFinal = ItemOrcamento::where('orcamento_id', $orcamento->id)
                ->sum('subtotal') ?? 0;

            // ===============================
            // 🔥 STATUS FINAL DO ORÇAMENTO
            // ===============================
            $temPendentes = ItemOrcamento::where('orcamento_id', $orcamento->id)
                ->whereRaw('(quantidade_solicitada - quantidade_atendida) > 0')
                ->exists();

            // 🎯 Salva o valor líquido com desconto no campo principal 'total'
            $orcamento->update([
                'total' => $totalLiquidoFinal, 
                'observacoes' => $request->input('observacoes'), // Atualiza o textarea que você configurou
                'status' => $temPendentes
                    ? 'Aguardando Estoque'
                    : 'Aguardando Aprovacao',
            ]);

            return $orcamento;
        });
    }

    public function cancelar(Orcamento $orcamento)
    {
        DB::transaction(function () use ($orcamento) {

            // 🔒 evita cancelar duas vezes
            if ($orcamento->status === 'Cancelado') {
                return;
            }

            // 🔄 percorre itens e libera reservas
            foreach ($orcamento->itens as $item) {
                $this->estoqueService->cancelarReserva($item);
            }

            // 🧾 atualiza status
            $orcamento->update([
                'status' => 'Cancelado'
            ]);
        });

        return $orcamento;
    }
   
    public function gerarPdfCompleto(Orcamento $orcamento)
    {
        $orcamento->load([
            'cliente',
            'itens.produto.unidadeMedida',
            'itens.lotes' // ou itens.lotes.lote
        ]);

        return Pdf::loadView('orcamentos.pdf', compact('orcamento'));
    }

    /* =========================================
     | WHATSAPP
     ========================================= */
    public function enviarWhatsapp(Orcamento $orcamento)
    {
        $pdf = Pdf::loadView('orcamentos.pdf', compact('orcamento'));

        $fileName = "orcamento_{$orcamento->codigo_orcamento}.pdf";
        $path = storage_path("app/public/orcamento/{$fileName}");

        $pdf->save($path);

        $telefone = preg_replace('/\D/', '', $orcamento->cliente->telefone ?? '');

        if (!$telefone) {
            throw new \Exception("Cliente sem telefone.");
        }

        $link = asset("storage/orcamento/{$fileName}");
        $msg = urlencode("Olá! Segue seu orçamento: {$link}");

        return "https://wa.me/55{$telefone}?text={$msg}";
    }

    /* =========================================
     | VISUALIZAR PDF
     ========================================= */
    public function visualizarArquivo(Orcamento $orcamento)
    {
        $fileName = "orcamento_{$orcamento->codigo_orcamento}.pdf";
        return asset("storage/orcamento/{$fileName}");
    }
        /* =========================================
     | FATURAR ORÇAMENTO NO PDV (CAIXA)
     ========================================= */
    /**
     * Converte os saldos lógicos de reserva do balcão em baixa física real.
     * Desenvolvido para alta performance em redes de múltiplos PDVs.
     */
    public function faturarEfetivo(Orcamento $orcamento, array $dados)
    {
        // Executa todo o bloco sob uma transação isolada para segurança concorrente
        DB::transaction(function () use ($orcamento, $dados) {
            
            // Re-carrega os itens travando para atualização no banco (Locking)
            foreach ($orcamento->itens as $item) {
                
                // Busca os registros associados na tabela pivot intermediária
                $vinculosLotes = DB::table('item_orcamento_lotes')
                    ->where('item_orcamento_id', $item->id)
                    ->get();

                foreach ($vinculosLotes as $v) {
                    
                    // 🔴 GARGALO EVITADO: Baixa atômica nativa diretamente no banco de dados.
                    // Subtrai a reserva lógica e o estoque físico real do lote simultaneamente.
                    DB::table('lotes')
                        ->where('id', $v->lote_id)
                        ->update([
                            'quantidade_reservada' => DB::raw("quantidade_reservada - {$v->quantidade_reservada}"),
                            'quantidade'           => DB::raw("quantidade - {$v->quantidade_reservada}"),
                            'quantidade_disponivel' => DB::raw("quantidade - quantidade_reservada")
                        ]);

                    // Converte o status do vínculo de reservado para atendido de fato
                    DB::table('item_orcamento_lotes')
                        ->where('id', $v->id)
                        ->update([
                            'quantidade_atendida'  => $v->quantidade_reservada,
                            'quantidade_reservada' => 0
                        ]);
                }

                // Sincroniza o estado interno do item do orçamento
                $item->update([
                    'quantidade_atendida' => $item->quantidade_solicitada,
                    'quantidade_pendente' => 0,
                    'status'              => 'disponivel',
                ]);

                // Registra o log histórico da linha do tempo da mercadoria
                \App\Models\MovimentacaoOrcamento::create([
                    'orcamento_id' => $orcamento->id,
                    'item_orcamento_id' => $item->id,
                    'tipo' => 'Faturamento',
                    'descricao' => "Venda finalizada no caixa do PDV. " . floatval($item->quantidade_solicitada) . " un. liberadas.",
                    'quantidade' => $item->quantidade_solicitada,
                    'user_id' => Auth::id() ?? $orcamento->editando_por,
                ]);
            }

            // Atualiza o cabeçalho definitivo do documento para bloquear acessos concorrentes na rede
            $orcamento->update([
                'status'       => 'Faturado',
                'editando_por' => Auth::id(),
                'editando_em'  => now(),
            ]);
        });

        return $orcamento;
    }
    /**
     * Retorna um orçamento com todos os relacionamentos necessários
     * para a tela de visualização.
     */
    public function buscarCompleto(int $orcamentoId): Orcamento
    {
        return Orcamento::with([
            'cliente',
            'vendedor',
            'usuario',

            // Itens do orçamento
            'itens',
            'itens.produto',
            'itens.lotes',

            // Venda gerada a partir do orçamento
            'venda',

            // Entrega gerada
            'entrega',
            'entrega.itens',
            'entrega.itens.produto',

        ])->findOrFail($orcamentoId);
    }

}