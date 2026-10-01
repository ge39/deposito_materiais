<?php

namespace App\Services;

use App\Models\AuditoriaSistema;
use App\Models\Produto;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;

class AjustePrecoService
{
    private const CAMPOS_PRECO = [
        'preco_venda',
        'preco_venda_2',
        'preco_venda_3',
    ];

    private const TIPOS_AJUSTE = [
        'acrescimo_percentual',
        'desconto_percentual',
        'acrescimo_valor',
        'desconto_valor',
    ];

    public function simular(array $dados): Collection
    {
        $campoPreco = (string) $dados['campo_preco'];
        $tipoAjuste = (string) $dados['tipo_ajuste'];
        $valorAjuste = (float) $dados['valor_ajuste'];

        $this->validarCampoPreco($campoPreco);
        $this->validarTipoAjuste($tipoAjuste);

        return $this->montarQueryProdutos($dados)
            ->orderBy('nome')
            ->get()
            ->map(function (Produto $produto) use (
                $campoPreco,
                $tipoAjuste,
                $valorAjuste
            ): array {
                return $this->montarLinhaSimulacao(
                    $produto,
                    $campoPreco,
                    $tipoAjuste,
                    $valorAjuste
                );
            });
    }

    public function aplicar(
        array $dados,
        ?int $usuarioId,
        ?string $ip,
        ?string $userAgent
    ): array {
        $campoPreco = (string) $dados['campo_preco'];
        $tipoAjuste = (string) $dados['tipo_ajuste'];
        $valorAjuste = (float) $dados['valor_ajuste'];

        $this->validarCampoPreco($campoPreco);
        $this->validarTipoAjuste($tipoAjuste);

        $operacaoUuid = (string) Str::uuid();

        return DB::transaction(function () use (
            $dados,
            $campoPreco,
            $tipoAjuste,
            $valorAjuste,
            $usuarioId,
            $ip,
            $userAgent,
            $operacaoUuid
        ): array {
            $ids = $this->montarQueryProdutos($dados)
                ->select('produtos.id')
                ->orderBy('produtos.id')
                ->pluck('produtos.id');

            if ($ids->isEmpty()) {
                throw new RuntimeException(
                    'Nenhum produto foi encontrado para aplicar o ajuste.'
                );
            }

            $produtos = Produto::query()
                ->whereIn('id', $ids)
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            $alterados = 0;
            $semAlteracao = 0;

            foreach ($produtos as $produto) {
                $precoAnterior = round(
                    (float) ($produto->{$campoPreco} ?? 0),
                    2
                );

                $novoPreco = $this->calcularNovoPreco(
                    $precoAnterior,
                    $tipoAjuste,
                    $valorAjuste
                );

                if ($novoPreco === $precoAnterior) {
                    $semAlteracao++;
                    continue;
                }

                $produto->{$campoPreco} = $novoPreco;
                $produto->save();

                AuditoriaSistema::create([
                    'usuario_id' => $usuarioId,
                    'operacao_uuid' => $operacaoUuid,
                    'modulo' => 'Ajuste de Preços',
                    'entidade' => Produto::class,
                    'entidade_id' => $produto->id,
                    'acao' => 'UPDATE',
                    'descricao' => $this->descricaoAuditoria(
                        $tipoAjuste,
                        $valorAjuste,
                        $campoPreco
                    ),
                    'dados_antes' => [
                        $campoPreco => $precoAnterior,
                    ],
                    'dados_depois' => [
                        $campoPreco => $novoPreco,
                    ],
                    'ip' => $ip,
                    'user_agent' => $userAgent,
                    'created_at' => now(),
                ]);

                $alterados++;
            }

            return [
                'operacao_uuid' => $operacaoUuid,
                'alterados' => $alterados,
                'sem_alteracao' => $semAlteracao,
                'total' => $produtos->count(),
            ];
        }, 3);
    }

    private function montarLinhaSimulacao(
        Produto $produto,
        string $campoPreco,
        string $tipoAjuste,
        float $valorAjuste
    ): array {
        $precoAtual = (float) ($produto->{$campoPreco} ?? 0);

        $novoPreco = $this->calcularNovoPreco(
            $precoAtual,
            $tipoAjuste,
            $valorAjuste
        );

        $estoqueDisponivel = (float) (
            $produto->disponivel_ajuste ?? 0
        );

        $custoMedio = (float) (
            $produto->custo_medio_ajuste ?? 0
        );

        $valorEstoqueCusto = round(
            $estoqueDisponivel * $custoMedio,
            2
        );

        $valorVendaAtual = round(
            $estoqueDisponivel * $precoAtual,
            2
        );

        $valorVendaNovo = round(
            $estoqueDisponivel * $novoPreco,
            2
        );

        return [
            'id' => (int) $produto->id,
            'nome' => (string) $produto->nome,
            'sku' => (string) ($produto->sku ?? ''),
            'categoria' => (string) (
                $produto->categoria?->nome ?? '-'
            ),
            'fornecedor' => (string) (
                $produto->fornecedor?->nome ?? '-'
            ),
            'unidade' => (string) (
                $produto->unidadeMedida?->nome ?? '-'
            ),
            'estoque_total' => (float) (
                $produto->estoque_total_ajuste ?? 0
            ),
            'quantidade_reservada' => (float) (
                $produto->quantidade_reservada_ajuste ?? 0
            ),
            'disponivel' => $estoqueDisponivel,
            'campo_preco' => $campoPreco,
            'preco_atual' => round(
                $precoAtual,
                2
            ),
            'novo_preco' => $novoPreco,
            'custo_medio' => round(
                $custoMedio,
                2
            ),
            'valor_estoque_custo' => $valorEstoqueCusto,
            'valor_venda_atual' => $valorVendaAtual,
            'valor_venda_novo' => $valorVendaNovo,
            'impacto_financeiro' => round(
                $valorVendaNovo - $valorVendaAtual,
                2
            ),
        ];
    }

    private function montarQueryProdutos(array $dados): Builder
    {
        $query = Produto::query()
            ->with([
                'categoria:id,nome',
                'fornecedor:id,nome',
                'unidadeMedida:id,nome',
            ])
            ->addSelect([
                'estoque_total_ajuste' => DB::table('lotes')
                    ->selectRaw('COALESCE(SUM(quantidade), 0)')
                    ->whereColumn(
                        'produto_id',
                        'produtos.id'
                    )
                    ->where('status', 1),

                'quantidade_reservada_ajuste' => DB::table('lotes')
                    ->selectRaw(
                        'COALESCE(SUM(quantidade_reservada), 0)'
                    )
                    ->whereColumn(
                        'produto_id',
                        'produtos.id'
                    )
                    ->where('status', 1),

                'disponivel_ajuste' => DB::table('lotes')
                    ->selectRaw(
                        'COALESCE(SUM(quantidade), 0)
                        - COALESCE(SUM(quantidade_reservada), 0)'
                    )
                    ->whereColumn(
                        'produto_id',
                        'produtos.id'
                    )
                    ->where('status', 1),

                'custo_medio_ajuste' => DB::table('lotes')
                    ->selectRaw(
                        'CASE
                            WHEN COALESCE(SUM(quantidade), 0) > 0
                            THEN
                                COALESCE(
                                    SUM(quantidade * preco_compra),
                                    0
                                )
                                / NULLIF(
                                    SUM(quantidade),
                                    0
                                )
                            ELSE 0
                        END'
                    )
                    ->whereColumn(
                        'produto_id',
                        'produtos.id'
                    )
                    ->where('status', 1),
            ]);

        $situacao = (string) (
            $dados['situacao'] ?? 'ativos'
        );

        if ($situacao === 'ativos') {
            $query->where('ativo', 1);
        } elseif ($situacao === 'inativos') {
            $query->where('ativo', 0);
        }

        $estoque = (string) (
            $dados['estoque'] ?? 'com_estoque'
        );

        if ($estoque === 'com_estoque') {
            $query->whereExists(
                function ($subquery): void {
                    $subquery
                        ->selectRaw('1')
                        ->from('lotes')
                        ->whereColumn(
                            'lotes.produto_id',
                            'produtos.id'
                        )
                        ->where(
                            'lotes.status',
                            1
                        )
                        ->groupBy(
                            'lotes.produto_id'
                        )
                        ->havingRaw(
                            'COALESCE(SUM(lotes.quantidade), 0)
                            - COALESCE(
                                SUM(lotes.quantidade_reservada),
                                0
                            ) > 0'
                        );
                }
            );
        } elseif ($estoque === 'sem_estoque') {
            $query->whereNotExists(
                function ($subquery): void {
                    $subquery
                        ->selectRaw('1')
                        ->from('lotes')
                        ->whereColumn(
                            'lotes.produto_id',
                            'produtos.id'
                        )
                        ->where(
                            'lotes.status',
                            1
                        )
                        ->groupBy(
                            'lotes.produto_id'
                        )
                        ->havingRaw(
                            'COALESCE(SUM(lotes.quantidade), 0)
                            - COALESCE(
                                SUM(lotes.quantidade_reservada),
                                0
                            ) > 0'
                        );
                }
            );
        }

        $escopo = (string) $dados['escopo'];

        match ($escopo) {
            'categoria' => $query->where(
                'categoria_id',
                (int) $dados['categoria_id']
            ),

            'fornecedor' => $query->where(
                'fornecedor_id',
                (int) $dados['fornecedor_id']
            ),

            'produto' => $query->where(
                'id',
                (int) $dados['produto_id']
            ),

            'selecionados' => $query->whereIn(
                'id',
                array_map(
                    'intval',
                    (array) (
                        $dados['produto_ids']
                        ?? []
                    )
                )
            ),

            'todos' => null,

            default => throw new InvalidArgumentException(
                'Escopo de ajuste inválido.'
            ),
        };

        return $query;
    }

    private function calcularNovoPreco(
        float $precoAtual,
        string $tipoAjuste,
        float $valorAjuste
    ): float {
        $novoPreco = match ($tipoAjuste) {
            'acrescimo_percentual' =>
                $precoAtual
                * (1 + ($valorAjuste / 100)),

            'desconto_percentual' =>
                $precoAtual
                * (1 - ($valorAjuste / 100)),

            'acrescimo_valor' =>
                $precoAtual + $valorAjuste,

            'desconto_valor' =>
                $precoAtual - $valorAjuste,

            default => $precoAtual,
        };

        return round(
            max(0, $novoPreco),
            2
        );
    }

    private function descricaoAuditoria(
        string $tipoAjuste,
        float $valorAjuste,
        string $campoPreco
    ): string {
        $rotuloTipo = match ($tipoAjuste) {
            'acrescimo_percentual' => 'Acréscimo percentual',
            'desconto_percentual' => 'Desconto percentual',
            'acrescimo_valor' => 'Acréscimo em valor',
            'desconto_valor' => 'Desconto em valor',
            default => 'Ajuste de preço',
        };

        $rotuloCampo = match ($campoPreco) {
            'preco_venda' => 'Preço de Venda 1',
            'preco_venda_2' => 'Preço de Venda 2',
            'preco_venda_3' => 'Preço de Venda 3',
            default => $campoPreco,
        };

        $valorFormatado = in_array(
            $tipoAjuste,
            [
                'acrescimo_percentual',
                'desconto_percentual',
            ],
            true
        )
            ? number_format(
                $valorAjuste,
                2,
                ',',
                '.'
            ) . '%'
            : 'R$ ' . number_format(
                $valorAjuste,
                2,
                ',',
                '.'
            );

        return $rotuloTipo
            . ' de '
            . $valorFormatado
            . ' em '
            . $rotuloCampo;
    }

    private function validarCampoPreco(
        string $campoPreco
    ): void {
        if (! in_array(
            $campoPreco,
            self::CAMPOS_PRECO,
            true
        )) {
            throw new InvalidArgumentException(
                'Campo de preço não permitido.'
            );
        }
    }

    private function validarTipoAjuste(
        string $tipoAjuste
    ): void {
        if (! in_array(
            $tipoAjuste,
            self::TIPOS_AJUSTE,
            true
        )) {
            throw new InvalidArgumentException(
                'Tipo de ajuste não permitido.'
            );
        }
    }
}
