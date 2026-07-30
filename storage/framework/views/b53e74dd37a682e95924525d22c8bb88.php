

<?php $__env->startSection('content'); ?>
<?php
    /*
    |--------------------------------------------------------------------------
    | CONTRATO VISUAL DA TRIAGEM
    |--------------------------------------------------------------------------
    |
    | A fonte inicial da tela é $dadosMateriais. As ocorrências já existentes
    | são usadas apenas para compor os totais e o histórico de cada material.
    |
    */
    $ocorrenciasCompatibilidade = collect($ocorrencias ?? []);

    $formatarQuantidadeTela = static function ($valor): string {
        return rtrim(
            rtrim(
                number_format((float) $valor, 2, ',', '.'),
                '0'
            ),
            ','
        );
    };

    $materiais = collect($dadosMateriais ?? [])
        ->map(function (
            $material,
            $chaveMaterial
        ) use ($romaneio, $ocorrenciasCompatibilidade) {
            $idPelaChave = collect($romaneio->itens ?? [])
                ->contains(
                    fn ($item) =>
                        (int) data_get($item, 'id')
                        === (int) $chaveMaterial
                )
                    ? (int) $chaveMaterial
                    : 0;

            $romaneioItemId = (int) (
                data_get($material, 'romaneio_item_id')
                ?? data_get($material, 'id')
                ?? data_get($material, 'item_id')
                ?? $idPelaChave
                ?? 0
            );

            $romaneioItem = collect($romaneio->itens ?? [])
                ->firstWhere('id', $romaneioItemId);

            $entregaItem = data_get($material, 'entrega_item')
                ?? data_get($material, 'entregaItem')
                ?? $romaneioItem?->entregaItem;

            $produtoInformado = data_get($material, 'produto');
            $produto = is_object($produtoInformado)
                || is_array($produtoInformado)
                    ? $produtoInformado
                    : (
                        data_get($entregaItem, 'produto')
                        ?? data_get($entregaItem, 'vendaItem.produto')
                        ?? data_get($entregaItem, 'itemOrcamento.produto')
                    );

            $nomeProduto = data_get($material, 'produto_nome')
                ?? (
                    is_string($produtoInformado)
                        ? $produtoInformado
                        : data_get($produto, 'nome')
                )
                ?? data_get($produto, 'descricao')
                ?? 'Produto não identificado';

            $codigoProduto = data_get($material, 'produto_codigo')
                ?? data_get($produto, 'codigo')
                ?? data_get($produto, 'id')
                ?? '-';

            $loteInformado = data_get($material, 'lote_original')
                ?? data_get($material, 'lote');

            $lote = is_object($loteInformado)
                || is_array($loteInformado)
                    ? $loteInformado
                    : (
                        data_get($entregaItem, 'vendaItem.lote')
                        ?? data_get($material, 'lote_model')
                    );

            $loteId = data_get($material, 'lote_id')
                ?? data_get($material, 'lote_original_id')
                ?? data_get($lote, 'id');

            $numeroLote = data_get($material, 'numero_lote')
                ?? data_get($material, 'lote_numero')
                ?? (
                    is_string($loteInformado)
                        ? $loteInformado
                        : data_get($lote, 'numero_lote')
                );

            $validadeLote = data_get($material, 'validade_lote')
                ?? data_get($material, 'lote_validade')
                ?? data_get($lote, 'validade_lote');

            $validadeProduto = data_get($material, 'validade_produto')
                ?? data_get($produto, 'validade_produto');

            $controlaValidade = (bool) (
                data_get($material, 'controla_validade')
                ?? data_get($produto, 'controla_validade')
                ?? false
            );

            $quantidadeSaida = (float) (
                data_get($material, 'quantidade_saida')
                ?? data_get($material, 'quantidade_conferida_saida')
                ?? data_get($material, 'quantidade_prevista')
                ?? data_get($romaneioItem, 'quantidade_conferida_saida')
                ?? data_get($romaneioItem, 'quantidade_prevista')
                ?? data_get($material, 'quantidade')
                ?? 0
            );

            $ocorrenciasMaterial = collect(
                data_get($material, 'ocorrencias', [])
            );

            if ($ocorrenciasMaterial->isEmpty() && $romaneioItemId > 0) {
                $ocorrenciasMaterial = $ocorrenciasCompatibilidade
                    ->filter(
                        fn ($ocorrencia) =>
                            (int) data_get($ocorrencia, 'romaneio_item_id')
                            === $romaneioItemId
                    )
                    ->values();
            }

            $ocorrenciasAtivas = $ocorrenciasMaterial
                ->reject(
                    fn ($ocorrencia) =>
                        in_array(
                            data_get($ocorrencia, 'status'),
                            ['Cancelada', 'Cancelado'],
                            true
                        )
                )
                ->values();

            $somarTipo = function (string $tipo) use (
                $material,
                $ocorrenciasAtivas,
                $romaneioItem
            ): float {
                $chaves = match ($tipo) {
                    'Devolucao' => [
                        'quantidade_devolvida',
                        'quantidades.Devolucao',
                        'quantidades.devolucao',
                    ],
                    'Recusa' => [
                        'quantidade_recusada',
                        'quantidades.Recusa',
                        'quantidades.recusa',
                    ],
                    'Avaria' => [
                        'quantidade_avariada',
                        'quantidades.Avaria',
                        'quantidades.avaria',
                    ],
                    'Extravio' => [
                        'quantidade_perdida',
                        'quantidade_extraviada',
                        'quantidades.Extravio',
                        'quantidades.extravio',
                    ],
                };

                foreach ($chaves as $chave) {
                    $valor = data_get($material, $chave);

                    if ($valor !== null) {
                        return (float) $valor;
                    }
                }

                $totalOcorrencias = $ocorrenciasAtivas
                    ->filter(function ($ocorrencia) use ($tipo) {
                        $tipoOcorrencia = data_get($ocorrencia, 'tipo')
                            ?? data_get(
                                $ocorrencia,
                                'classificacao_inicial'
                            );

                        return strcasecmp(
                            (string) $tipoOcorrencia,
                            $tipo
                        ) === 0;
                    })
                    ->sum(
                        fn ($ocorrencia) =>
                            (float) data_get(
                                $ocorrencia,
                                'quantidade_envolvida',
                                0
                            )
                    );

                if ($totalOcorrencias > 0) {
                    return (float) $totalOcorrencias;
                }

                $campoRomaneio = match ($tipo) {
                    'Devolucao' => 'quantidade_devolvida',
                    'Recusa' => 'quantidade_recusada',
                    'Avaria' => 'quantidade_avariada',
                    'Extravio' => 'quantidade_perdida',
                };

                return (float) (
                    data_get($romaneioItem, $campoRomaneio)
                    ?? 0
                );
            };

            $quantidadesPorTipo = [
                'Devolucao' => $somarTipo('Devolucao'),
                'Recusa' => $somarTipo('Recusa'),
                'Avaria' => $somarTipo('Avaria'),
                'Extravio' => $somarTipo('Extravio'),
            ];

            $quantidadeAfetadaCalculada = array_sum(
                $quantidadesPorTipo
            );

            $quantidadeAfetada = (float) (
                data_get($material, 'quantidade_afetada')
                ?? data_get($material, 'quantidade_com_ocorrencia')
                ?? $quantidadeAfetadaCalculada
            );

            $saldoNormal = max(
                0,
                (float) (
                    data_get($material, 'saldo_normal')
                    ?? data_get(
                        $material,
                        'quantidade_sem_ocorrencia'
                    )
                    ?? ($quantidadeSaida - $quantidadeAfetada)
                )
            );

            $triagensPendentes = $ocorrenciasAtivas
                ->filter(
                    fn ($ocorrencia) =>
                        data_get($ocorrencia, 'triagem_status')
                        !== 'Concluida'
                )
                ->count();

            $triagensConcluidas = $ocorrenciasAtivas
                ->filter(
                    fn ($ocorrencia) =>
                        data_get($ocorrencia, 'triagem_status')
                        === 'Concluida'
                )
                ->count();

            $anexos = $ocorrenciasAtivas
                ->flatMap(function ($ocorrencia) {
                    return collect(
                        data_get($ocorrencia, 'anexos', [])
                    )->map(function ($anexo) {
                        $caminho = (string) data_get(
                            $anexo,
                            'caminho',
                            ''
                        );

                        $arquivoPublico =
                            str_starts_with($caminho, 'image/')
                            || str_starts_with(
                                $caminho,
                                'uploads/'
                            );

                        return [
                            'tipo' => data_get($anexo, 'tipo'),
                            'descricao' => data_get(
                                $anexo,
                                'descricao'
                            ),
                            'mime_type' => data_get(
                                $anexo,
                                'mime_type'
                            ),
                            'url' => $arquivoPublico
                                ? asset($caminho)
                                : asset('storage/' . $caminho),
                        ];
                    });
                })
                ->values();

            $ocorrenciaIntegral =
                $quantidadeSaida > 0.0001
                && $quantidadeAfetada >= ($quantidadeSaida - 0.0001);

            $situacao = match (true) {
                $quantidadeAfetada <= 0.0001 =>
                    'Sem ocorrência',
                $triagensPendentes > 0 =>
                    'Triagem pendente',
                $ocorrenciaIntegral =>
                    'Ocorrência integral',
                default =>
                    'Ocorrência parcial',
            };

            return [
                'romaneio_item_id' => $romaneioItemId,
                'entrega_item_id' => (int) (
                    data_get($material, 'entrega_item_id')
                    ?? data_get($romaneioItem, 'entrega_item_id')
                    ?? data_get($entregaItem, 'id')
                    ?? 0
                ),
                'produto' => $nomeProduto,
                'produto_codigo' => $codigoProduto,
                'lote_id' => $loteId ? (int) $loteId : null,
                'lote' => $numeroLote,
                'validade_lote' => $validadeLote,
                'validade_produto' => $validadeProduto,
                'controla_validade' => $controlaValidade,
                'quantidade_saida' => round($quantidadeSaida, 3),
                'quantidade_afetada' => round(
                    $quantidadeAfetada,
                    3
                ),
                'saldo_normal' => round($saldoNormal, 3),
                'quantidades' => $quantidadesPorTipo,
                'ocorrencias_count' => $ocorrenciasAtivas->count(),
                'triagens_pendentes' => $triagensPendentes,
                'triagens_concluidas' => $triagensConcluidas,
                'situacao' => $situacao,
                'anexos' => $anexos,
            ];
        })
        ->filter(
            fn ($material) =>
                (int) $material['romaneio_item_id'] > 0
        )
        ->values();

    $totalProdutos = (int) (
        data_get($totaisTriagem ?? [], 'produtos_transportados')
        ?? data_get($totaisTriagem ?? [], 'total_produtos')
        ?? $materiais->count()
    );

    $quantidadeSaidaTotal = (float) (
        data_get($totaisTriagem ?? [], 'quantidade_saida')
        ?? data_get($totaisTriagem ?? [], 'quantidade_total')
        ?? $materiais->sum('quantidade_saida')
    );

    $produtosComProblema = (int) (
        data_get($totaisTriagem ?? [], 'produtos_com_problema')
        ?? $materiais
            ->filter(
                fn ($material) =>
                    $material['quantidade_afetada'] > 0.0001
            )
            ->count()
    );

    $produtosSemOcorrencia = (int) (
        data_get($totaisTriagem ?? [], 'produtos_sem_ocorrencia')
        ?? $materiais
            ->filter(
                fn ($material) =>
                    $material['quantidade_afetada'] <= 0.0001
            )
            ->count()
    );

    $todasTriagensConcluidas = $materiais->every(
        fn ($material) =>
            $material['quantidade_afetada'] <= 0.0001
            || $material['triagens_pendentes'] === 0
    );

    $tiposResultadoNormalizados = collect(
        $tiposResultado ?? [
            'Devolucao' => 'Devolução',
            'Recusa' => 'Recusa',
            'Avaria' => 'Avaria',
            'Extravio' => 'Extravio',
        ]
    )->mapWithKeys(function ($rotulo, $valor) {
        if (is_int($valor)) {
            return [
                (string) $rotulo =>
                    str_replace('_', ' ', (string) $rotulo),
            ];
        }

        return [(string) $valor => (string) $rotulo];
    });

    $obterValoresOpcao = function (
        array $chaves,
        array $padrao
    ) use ($opcoesTriagem): array {
        $opcoes = null;

        foreach ($chaves as $chave) {
            $opcoes = data_get($opcoesTriagem ?? [], $chave);

            if ($opcoes !== null) {
                break;
            }
        }

        return collect($opcoes ?? $padrao)
            ->map(
                fn ($rotulo, $valor) =>
                    is_int($valor) ? $rotulo : $valor
            )
            ->values()
            ->all();
    };

    $opcoesFormulario = [
        'embalagens' => $obterValoresOpcao(
            ['embalagens', 'embalagem'],
            [
                'Intacta',
                'Rasgada',
                'Aberta',
                'Furada',
                'Amassada',
                'Quebrada',
                'Lacre_violado',
                'Sem_embalagem',
                'Nao_se_aplica',
            ]
        ),
        'conteudos' => $obterValoresOpcao(
            ['conteudos', 'conteudo'],
            [
                'Preservado',
                'Parcial',
                'Vazando',
                'Derramado',
                'Espalhado',
                'Endurecido',
                'Molhado',
                'Misturado',
                'Contaminado',
                'Ausente',
                'Nao_se_aplica',
            ]
        ),
        'integridades' => $obterValoresOpcao(
            ['integridades', 'integridade'],
            [
                'Integro',
                'Dano_leve',
                'Dano_parcial',
                'Quebrado',
                'Deformado',
                'Incompleto',
                'Perda_total',
                'Avaliacao_inconclusiva',
            ]
        ),
        'statusValidade' => $obterValoresOpcao(
            [
                'statusValidade',
                'status_validade',
                'validade_status',
            ],
            [
                'Dentro_validade',
                'Proximo_vencimento',
                'Vencido',
                'Data_ilegivel',
                'Sem_identificacao',
                'Nao_se_aplica',
            ]
        ),
        'reaproveitamentos' => $obterValoresOpcao(
            ['reaproveitamentos', 'reaproveitamento'],
            [
                'Uso_normal',
                'Reembalagem',
                'Troca_embalagem',
                'Reparo_embalagem',
                'Venda_granel',
                'Venda_com_avaria',
                'Uso_interno',
                'Aproveitamento_parcial',
                'Retorno_fornecedor',
                'Reciclagem',
                'Sem_reaproveitamento',
            ]
        ),
        'destinos' => $obterValoresOpcao(
            ['destinos', 'destino_sugerido'],
            [
                'Sem_movimentacao',
                'Quarentena',
                'Reintegracao',
                'Perda',
                'Reposicao',
            ]
        ),
    ];

    $materiaisJson = $materiais
        ->keyBy('romaneio_item_id')
        ->map(
            fn ($material) => [
                'id' => $material['romaneio_item_id'],
                'produto' => $material['produto'],
                'lote_id' => $material['lote_id'],
                'lote' => $material['lote'],
                'validade_lote' => $material['validade_lote'],
                'validade_produto' =>
                    $material['validade_produto'],
                'controla_validade' =>
                    $material['controla_validade'],
                'quantidade_saida' =>
                    $material['quantidade_saida'],
                'quantidade_afetada' =>
                    $material['quantidade_afetada'],
                'saldo_normal' => $material['saldo_normal'],
                'anexos' => $material['anexos'],
            ]
        );
?>

<style>
    .triagem-page {
        --triagem-border: #d6dee6;
        --triagem-header: #69757f;
        --triagem-soft: #f4f8ff;
        --triagem-primary: #0d6efd;
        margin-left: calc(50% - 50vw + 8px);
        max-width: none;
        width: calc(100vw - 16px);
    }

    .triagem-page .page-title {
        font-size: 1.65rem;
        font-weight: 700;
    }

    .triagem-page .kpi-card,
    .triagem-page .section-card {
        background: #fff;
        border: 1px solid var(--triagem-border);
        border-radius: .65rem;
    }

    .triagem-page .kpi-card {
        height: 100%;
        min-height: 94px;
    }

    .triagem-page .kpi-icon {
        align-items: center;
        border-radius: 50%;
        display: inline-flex;
        flex: 0 0 46px;
        font-size: 1.25rem;
        height: 46px;
        justify-content: center;
        width: 46px;
    }

    .triagem-page .section-card {
        overflow: hidden;
    }

    .triagem-page .section-header {
        align-items: center;
        background: var(--triagem-header);
        color: #fff;
        display: flex;
        font-weight: 700;
        gap: .75rem;
        justify-content: space-between;
        padding: .75rem 1rem;
    }

    .triagem-page .material-table {
        margin-bottom: 0;
        min-width: 1120px;
    }

    .triagem-page .material-table th {
        background: #212529;
        color: #fff;
        font-size: .76rem;
        letter-spacing: .02em;
        text-transform: uppercase;
        vertical-align: middle;
    }

    .triagem-page .material-table td {
        vertical-align: middle;
    }

    .triagem-page .material-table tbody tr:hover {
        background: #f8fbff;
    }

    .triagem-page .material-table tr.selecionado {
        background: #eaf3ff;
        box-shadow: inset 4px 0 0 var(--triagem-primary);
    }

    .triagem-page .produto-nome {
        font-weight: 700;
    }

    .triagem-page .produto-meta {
        color: #6c757d;
        font-size: .78rem;
    }

    .triagem-page .quantidade-destaque {
        font-size: .95rem;
        font-weight: 700;
        white-space: nowrap;
    }

    .triagem-page .avaliacao-panel {
        background: var(--triagem-soft);
        border: 2px solid #4b9cff;
        border-radius: .65rem;
        overflow: hidden;
    }

    .triagem-page .avaliacao-title {
        align-items: center;
        background: #e7f1ff;
        border-bottom: 1px solid #b9d6ff;
        display: flex;
        gap: .75rem;
        justify-content: space-between;
        padding: .85rem 1rem;
    }

    .triagem-page .resumo-selecionado {
        background: #fff;
        border-bottom: 1px solid #c9d8e8;
        padding: .8rem 1rem;
    }

    .triagem-page .avaliacao-table {
        font-size: .84rem;
        margin-bottom: 0;
        min-width: 1180px;
        table-layout: fixed;
        width: 100%;
    }

    .triagem-page .avaliacao-table th {
        background: #f8f9fa;
        font-size: .72rem;
        text-align: center;
        text-transform: uppercase;
        vertical-align: middle;
    }

    .triagem-page .avaliacao-table td {
        min-width: 130px;
        vertical-align: top;
    }

    .triagem-page .avaliacao-table td:first-child {
        min-width: 100px;
        width: 100px;
    }

    .triagem-page .avaliacao-table td:last-child {
        min-width: 65px;
        width: 65px;
    }

    .triagem-page .avaliacao-table .form-select,
    .triagem-page .avaliacao-table .form-control {
        font-size: .8rem;
        min-height: 34px;
        padding: .3rem .45rem;
    }

    .triagem-page .validade-automatica {
        background-color: #e9ecef;
        pointer-events: none;
    }

    .triagem-page .conferencia {
        align-items: center;
        border-top: 1px solid #b9d6ff;
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        justify-content: space-between;
        padding: .8rem 1rem;
    }

    .triagem-page .conferencia-item {
        border-right: 1px solid #c9d6e4;
        min-width: 190px;
        padding-right: 1rem;
    }

    .triagem-page .evidencia-upload {
        background: #f8fbff;
        border: 1px dashed #86b7fe;
        border-radius: .5rem;
        padding: .9rem;
    }

    .triagem-page .evidencia-miniatura {
        align-items: center;
        background: #fff;
        border: 1px solid #d5dce3;
        border-radius: .45rem;
        display: flex;
        height: 92px;
        justify-content: center;
        overflow: hidden;
    }

    .triagem-page .evidencia-miniatura img {
        height: 100%;
        object-fit: cover;
        width: 100%;
    }

    .triagem-page .arquivos-selecionados-grid {
        display: grid;
        gap: .65rem;
        grid-template-columns: repeat(
            auto-fill,
            minmax(150px, 1fr)
        );
        margin-top: .75rem;
    }

    .triagem-page .arquivo-selecionado {
        background: #fff;
        border: 1px solid #dee2e6;
        border-radius: .4rem;
        display: flex;
        flex-direction: column;
        gap: .5rem;
        min-width: 0;
        padding: .45rem;
        position: relative;
    }

    .triagem-page .arquivo-preview {
        align-items: center;
        background: #eef3f8;
        border-radius: .3rem;
        display: flex;
        height: 105px;
        justify-content: center;
        overflow: hidden;
    }

    .triagem-page .arquivo-preview img {
        height: 100%;
        object-fit: cover;
        width: 100%;
    }

    .triagem-page .arquivo-dados {
        line-height: 1.2;
        min-width: 0;
    }

    .triagem-page .remover-evidencia-selecionada {
        align-items: center;
        border-radius: 50%;
        display: inline-flex;
        height: 30px;
        justify-content: center;
        padding: 0;
        position: absolute;
        right: .65rem;
        top: .65rem;
        width: 30px;
        z-index: 2;
    }

    .triagem-page .action-footer {
        align-items: center;
        background: #fff;
        border-top: 1px solid var(--triagem-border);
        bottom: 0;
        display: flex;
        flex-wrap: wrap;
        gap: .75rem;
        justify-content: space-between;
        padding: .8rem 1rem;
        position: sticky;
        z-index: 10;
    }

    .triagem-page .finalizacao-card {
        border: 1px solid #9ec5fe;
        border-radius: .65rem;
        overflow: hidden;
    }

    @media (max-width: 991.98px) {
        .triagem-page {
            margin-left: 0;
            width: 100%;
        }

        .triagem-page .page-title {
            font-size: 1.35rem;
        }

        .triagem-page .action-footer {
            position: static;
        }
    }
</style>

<div class="container-fluid px-2 px-xl-3 py-3 triagem-page">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <div class="page-title">
                <i class="bi bi-ui-checks-grid me-2"></i>
                Triagem de falhas da entrega
            </div>
            <div class="text-muted">
                Romaneio
                <?php echo e($romaneio->codigo_romaneio ?? '#' . $romaneio->id); ?>

                <?php if($romaneio->entrega_id): ?>
                    • Entrega #<?php echo e($romaneio->entrega_id); ?>

                <?php endif; ?>
            </div>
        </div>

        <?php if($romaneio->entrega_id): ?>
            <a
                href="<?php echo e(route(
                    'entregas.retorno',
                    $romaneio->entrega_id
                )); ?>"
                class="btn btn-outline-secondary"
                data-bs-toggle="tooltip"
                data-bs-placement="bottom"
                title="Retornar ao fluxo operacional do retorno."
            >
                <i class="bi bi-arrow-left me-1"></i>
                Voltar ao retorno
            </a>
        <?php endif; ?>
    </div>

    <?php if($errors->any()): ?>
        <div class="alert alert-danger">
            <div class="fw-bold mb-1">
                Não foi possível salvar a triagem:
            </div>
            <ul class="mb-0">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $erro): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($erro); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if(session('success')): ?>
        <div
            class="alert alert-success alert-dismissible fade show"
            role="alert"
        >
            <i class="bi bi-check-circle-fill me-1"></i>
            <strong><?php echo e(session('success')); ?></strong>
            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
                aria-label="Fechar"
                data-bs-toggle="tooltip"
                data-bs-placement="left"
                title="Fechar esta mensagem."
            ></button>
        </div>
    <?php endif; ?>

    <?php if(session('error')): ?>
        <div
            class="alert alert-danger alert-dismissible fade show"
            role="alert"
        >
            <i class="bi bi-exclamation-triangle-fill me-1"></i>
            <strong><?php echo e(session('error')); ?></strong>
            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
                aria-label="Fechar"
                data-bs-toggle="tooltip"
                title="Fechar esta mensagem."
            ></button>
        </div>
    <?php endif; ?>

    <div class="row g-3 mb-3">
        <div class="col-6 col-xl-3">
            <div class="kpi-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <span class="kpi-icon bg-primary text-white">
                        <i class="bi bi-box-seam"></i>
                    </span>
                    <div>
                        <div class="text-muted">
                            Produtos transportados
                        </div>
                        <div class="fs-3 fw-bold text-primary">
                            <?php echo e($totalProdutos); ?>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="kpi-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <span class="kpi-icon bg-info text-white">
                        <i class="bi bi-boxes"></i>
                    </span>
                    <div>
                        <div class="text-muted">
                            Quantidade de saída
                        </div>
                        <div class="fs-3 fw-bold text-info">
                            <?php echo e($formatarQuantidadeTela(
                                $quantidadeSaidaTotal
                            )); ?>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="kpi-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <span class="kpi-icon bg-danger text-white">
                        <i class="bi bi-exclamation-triangle"></i>
                    </span>
                    <div>
                        <div class="text-muted">
                            Produtos com problema
                        </div>
                        <div class="fs-3 fw-bold text-danger">
                            <?php echo e($produtosComProblema); ?>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="kpi-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <span class="kpi-icon bg-success text-white">
                        <i class="bi bi-check-circle"></i>
                    </span>
                    <div>
                        <div class="text-muted">
                            Produtos sem ocorrência
                        </div>
                        <div class="fs-3 fw-bold text-success">
                            <?php echo e($produtosSemOcorrencia); ?>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="alert alert-info mb-3">
        <i class="bi bi-info-circle me-1"></i>
        Todos os produtos transportados aparecem abaixo. Informe somente os
        materiais que apresentaram problema. O saldo sem ocorrência será
        considerado entregue na conclusão geral.
    </div>

    <div class="section-card mb-3">
        <div class="section-header">
            <span>
                <i class="bi bi-boxes me-1"></i>
                Produtos transportados
            </span>
            <span class="small">
                <?php echo e($materiais->count()); ?>

                <?php echo e($materiais->count() === 1 ? 'produto' : 'produtos'); ?>

            </span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover material-table">
                <thead>
                    <tr>
                        <th>Produto</th>
                        <th style="width: 190px;">Lote original</th>
                        <th class="text-end" style="width: 130px;">
                            Saída
                        </th>
                        <th class="text-end" style="width: 140px;">
                            Afetada
                        </th>
                        <th class="text-end" style="width: 140px;">
                            Saldo normal
                        </th>
                        <th style="width: 180px;">Situação</th>
                        <th class="text-center" style="width: 180px;">
                            Ação
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $materiais; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $material): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $semSaldo =
                                $material['saldo_normal'] <= 0.0001;

                            $ocorrenciaIntegral =
                                $material['quantidade_saida'] > 0.0001
                                && $material['quantidade_afetada']
                                    >= (
                                        $material['quantidade_saida']
                                        - 0.0001
                                    );
                        ?>

                        <tr
                            id="linha-material-<?php echo e($material['romaneio_item_id']); ?>"
                            data-material-id="<?php echo e($material['romaneio_item_id']); ?>"
                        >
                            <td>
                                <div class="produto-nome">
                                    <?php echo e($material['produto']); ?>

                                </div>
                                <div class="produto-meta">
                                    Código:
                                    <?php echo e($material['produto_codigo']); ?>


                                    <?php if($material['ocorrencias_count'] > 0): ?>
                                        •
                                        <?php echo e($material['ocorrencias_count']); ?>

                                        ocorrência(s) registrada(s)
                                    <?php endif; ?>
                                </div>
                            </td>

                            <td>
                                <?php if($material['lote_id']): ?>
                                    <strong class="d-block">
                                        <?php echo e($material['lote']
                                            ?? 'Lote #' . $material['lote_id']); ?>

                                    </strong>
                                <?php else: ?>
                                    <span class="badge text-bg-danger">
                                        Não identificado
                                    </span>
                                <?php endif; ?>

                                <small class="text-muted">
                                    <?php if(! $material['controla_validade']): ?>
                                        Validade não aplicável
                                    <?php elseif($material['validade_lote']): ?>
                                        Validade:
                                        <?php echo e(\Carbon\Carbon::parse(
                                            $material['validade_lote']
                                        )->format('d/m/Y')); ?>

                                    <?php else: ?>
                                        Validade não identificada
                                    <?php endif; ?>
                                </small>
                            </td>

                            <td class="text-end quantidade-destaque">
                                <?php echo e($formatarQuantidadeTela(
                                    $material['quantidade_saida']
                                )); ?>

                            </td>

                            <td class="text-end quantidade-destaque text-danger">
                                <?php echo e($formatarQuantidadeTela(
                                    $material['quantidade_afetada']
                                )); ?>

                            </td>

                            <td class="text-end quantidade-destaque text-success">
                                <?php echo e($formatarQuantidadeTela(
                                    $material['saldo_normal']
                                )); ?>

                            </td>

                            <td>
                                <?php if(
                                    $material['quantidade_afetada']
                                    <= 0.0001
                                ): ?>
                                    <span class="badge text-bg-secondary">
                                        Sem ocorrência
                                    </span>
                                <?php elseif(
                                    $material['triagens_pendentes']
                                    > 0
                                ): ?>
                                    <span class="badge text-bg-warning">
                                        Triagem pendente
                                    </span>
                                <?php elseif($ocorrenciaIntegral): ?>
                                    <span class="badge text-bg-danger">
                                        Ocorrência integral
                                    </span>
                                <?php else: ?>
                                    <span class="badge text-bg-warning">
                                        Ocorrência parcial
                                    </span>
                                <?php endif; ?>
                            </td>

                            <td class="text-center">
                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-danger btn-informar-problema"
                                    data-material-id="<?php echo e($material['romaneio_item_id']); ?>"
                                    data-bs-toggle="tooltip"
                                    data-bs-placement="left"
                                    title="<?php echo e($semSaldo
                                            ? 'Toda a quantidade de saída já possui ocorrência.'
                                            : 'Informar uma falha somente para este produto.'); ?>"
                                    <?php if($semSaldo): echo 'disabled'; endif; ?>
                                >
                                    <i class="bi bi-exclamation-triangle me-1"></i>
                                    Informar problema
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td
                                colspan="7"
                                class="text-center text-muted py-5"
                            >
                                <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                Nenhum produto transportado foi carregado
                                para este romaneio.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <form
        method="POST"
        action="<?php echo e(route(
            'romaneios.ocorrencias.triagem.salvar',
            $romaneio
        )); ?>"
        id="formTriagem"
        enctype="multipart/form-data"
        class="d-none"
    >
        <?php echo csrf_field(); ?>
        <?php echo method_field('PATCH'); ?>

        <input
            type="hidden"
            name="romaneio_item_id"
            id="romaneioItemId"
            value="<?php echo e(old('romaneio_item_id')); ?>"
        >

        <div class="avaliacao-panel mb-3" id="painelAvaliacao">
            <div class="avaliacao-title">
                <div>
                    <i class="bi bi-eye me-1 text-primary"></i>
                    <strong>Avaliação do material — </strong>
                    <span id="produtoSelecionado"></span>
                </div>

                <button
                    type="button"
                    class="btn btn-sm btn-outline-secondary"
                    id="btnFecharAvaliacao"
                    data-bs-toggle="tooltip"
                    data-bs-placement="left"
                    title="Fechar esta avaliação sem salvar."
                    aria-label="Fechar avaliação"
                >
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <div class="resumo-selecionado">
                <div class="row g-3">
                    <div class="col-md-4">
                        <span class="text-muted d-block">
                            Lote original
                        </span>
                        <strong id="loteSelecionado">
                            Não identificado
                        </strong>
                    </div>

                    <div class="col-md-4">
                        <span class="text-muted d-block">
                            Quantidade de saída
                        </span>
                        <strong id="quantidadeSaidaSelecionada">
                            0
                        </strong>
                    </div>

                    <div class="col-md-4">
                        <span class="text-muted d-block">
                            Saldo disponível para nova ocorrência
                        </span>
                        <strong
                            class="text-success"
                            id="saldoSelecionado"
                        >
                            0
                        </strong>
                    </div>
                </div>
            </div>

            <div class="p-3 border-bottom">
                <div class="row g-3">
                    <div class="col-lg-6">
                        <label
                            for="tipoResultado"
                            class="form-label fw-bold"
                        >
                            Tipo do problema
                        </label>
                        <select
                            name="tipo_resultado"
                            id="tipoResultado"
                            class="form-select"
                            required
                        >
                            <option value="">Selecione...</option>
                            <?php $__currentLoopData = $tiposResultadoNormalizados; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $valor => $rotulo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option
                                    value="<?php echo e($valor); ?>"
                                    <?php if(
                                        old('tipo_resultado')
                                        === $valor
                                    ): echo 'selected'; endif; ?>
                                >
                                    <?php echo e($rotulo); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <div class="form-text">
                            Extravio não possui material físico para avaliação.
                        </div>
                    </div>

                    <div class="col-lg-3">
                        <label
                            for="quantidadeAfetada"
                            class="form-label fw-bold"
                        >
                            Quantidade afetada
                        </label>
                        <input
                            type="number"
                            name="quantidade_afetada"
                            id="quantidadeAfetada"
                            class="form-control"
                            min="0.50"
                            step="0.50"
                            value="<?php echo e(old('quantidade_afetada')); ?>"
                            required
                        >
                        <div class="form-text">
                            Em devolução, recusa e avaria, o total é calculado
                            automaticamente pela soma das avaliações. Use
                            intervalos de 0,50.
                        </div>
                    </div>

                    <div class="col-lg-3">
                        <label class="form-label fw-bold">
                            Saldo normal após salvar
                        </label>
                        <div
                            class="form-control bg-light fw-bold"
                            id="saldoAposOcorrencia"
                        >
                            0
                        </div>
                    </div>
                </div>

                <div
                    class="alert alert-danger mt-3 mb-0 d-none"
                    id="alertaLote"
                >
                    <i class="bi bi-lock-fill me-1"></i>
                    Este material não possui lote original identificado.
                    Devolução, recusa e avaria ficam bloqueadas para preservar
                    a rastreabilidade do estoque.
                </div>
            </div>

            <div id="blocoAvaliacaoFisica" class="d-none">
                <div class="p-3 pb-2">
                    <div class="fw-bold">
                        <i class="bi bi-clipboard-check me-1"></i>
                        Avaliação física
                    </div>
                    <div class="small text-muted">
                        Comece com uma avaliação. Adicione outra linha somente
                        quando parte da quantidade possuir condição ou destino
                        diferente.
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered avaliacao-table">
                        <thead>
                            <tr>
                                <th>Qtd.</th>
                                <th>Embalagem</th>
                                <th>Conteúdo</th>
                                <th>Integridade</th>
                                <th>Validade</th>
                                <th>Reaproveitamento</th>
                                <th>Destino sugerido</th>
                                <th>Ação</th>
                            </tr>
                        </thead>
                        <tbody id="avaliacoesBody"></tbody>
                    </table>
                </div>

                <div class="p-2">
                    <button
                        type="button"
                        class="btn btn-sm btn-outline-primary"
                        id="btnAdicionarAvaliacao"
                        data-bs-toggle="tooltip"
                        data-bs-placement="top"
                        title="Adicionar outra condição ou destino para parte deste mesmo material."
                    >
                        <i class="bi bi-plus-lg me-1"></i>
                        Adicionar outra avaliação
                    </button>
                </div>

                <div class="conferencia">
                    <div class="conferencia-item">
                        <span class="text-muted">
                            Quantidade afetada:
                        </span>
                        <strong
                            class="ms-1"
                            id="quantidadeOcorrencia"
                        >
                            0
                        </strong>
                    </div>

                    <div class="conferencia-item">
                        <span class="text-muted">
                            Quantidade avaliada:
                        </span>
                        <strong
                            class="ms-1"
                            id="quantidadeAvaliada"
                        >
                            0
                        </strong>
                    </div>

                    <span
                        class="badge text-bg-warning fs-6"
                        id="statusConferencia"
                    >
                        Aguardando conferência
                    </span>
                </div>
            </div>

            <div
                class="alert alert-info rounded-0 border-0 border-top mb-0 d-none"
                id="avisoExtravio"
            >
                <i class="bi bi-info-circle me-1"></i>
                Extravio registra a quantidade não localizada. Nenhuma
                avaliação física ou movimentação de retorno será criada nesta
                etapa.
            </div>

            <div class="row g-3 p-3">
                <div class="col-xl-6">
                    <div class="section-card h-100">
                        <div class="section-header">
                            <span>
                                <i class="bi bi-paperclip me-1"></i>
                                Evidências existentes
                            </span>
                        </div>
                        <div class="p-3" id="evidenciasExistentes">
                            <div class="text-muted">
                                Nenhuma evidência registrada para este
                                material.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-6">
                    <div class="section-card h-100">
                        <div class="section-header">
                            <span>
                                <i class="bi bi-camera me-1"></i>
                                Adicionar evidências
                            </span>
                        </div>
                        <div class="p-3">
                            <div class="evidencia-upload">
                                <label
                                    for="evidencias"
                                    class="form-label fw-bold"
                                >
                                    Fotos ou documentos
                                </label>
                                <input
                                    type="file"
                                    name="evidencias[]"
                                    id="evidencias"
                                    class="form-control"
                                    accept="image/*,.pdf"
                                    multiple
                                >
                                <div class="form-text">
                                    Até 4 arquivos, com no máximo 10 MB cada.
                                </div>
                                <div
                                    id="arquivosSelecionados"
                                    class="arquivos-selecionados-grid"
                                ></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <label
                        for="justificativaTriagem"
                        class="form-label fw-bold"
                    >
                        Observação da triagem
                    </label>
                    <textarea
                        name="justificativa_triagem"
                        id="justificativaTriagem"
                        class="form-control"
                        rows="3"
                        maxlength="5000"
                        placeholder="Descreva o problema observado e as informações relevantes para a tratativa."
                    ><?php echo e(old('justificativa_triagem')); ?></textarea>
                </div>
            </div>

            <div class="action-footer">
                <span class="small text-muted" id="orientacaoSalvar">
                    Preencha o tipo e a quantidade afetada.
                </span>

                <div class="d-flex flex-wrap gap-2">
                    <button
                        type="button"
                        class="btn btn-outline-secondary"
                        id="btnCancelarAvaliacao"
                        data-bs-toggle="tooltip"
                        title="Fechar esta avaliação sem salvar."
                    >
                        <i class="bi bi-x-circle me-1"></i>
                        Cancelar
                    </button>

                    <button
                        type="submit"
                        class="btn btn-primary"
                        id="btnSalvarAvaliacao"
                        data-bs-toggle="tooltip"
                        title="Salvar somente o problema deste material."
                        disabled
                    >
                        <i class="bi bi-check-circle me-1"></i>
                        Salvar avaliação deste material
                    </button>
                </div>
            </div>
        </div>
    </form>

    <div class="finalizacao-card mb-3">
        <div class="section-header bg-primary">
            <span>
                <i class="bi bi-flag me-1"></i>
                Conclusão geral do retorno
            </span>
        </div>

        <div class="p-3 bg-white">
            <div class="row g-3 align-items-center">
                <div class="col-xl-8">
                    <?php if($produtosComProblema === 0): ?>
                        <div class="fw-bold text-warning">
                            Nenhum produto com problema foi informado.
                        </div>
                        <div class="small text-muted">
                            Utilize “Informar problema” em pelo menos um
                            produto. Para entrega totalmente normal, retorne à
                            tela anterior e use o fluxo normal.
                        </div>
                    <?php elseif(! $todasTriagensConcluidas): ?>
                        <div class="fw-bold text-warning">
                            Existem ocorrências com triagem pendente.
                        </div>
                        <div class="small text-muted">
                            Salve todas as avaliações antes de concluir o
                            retorno.
                        </div>
                    <?php elseif(! $romaneio->entrega_id): ?>
                        <div class="fw-bold text-warning">
                            Entrega vinculada não identificada.
                        </div>
                        <div class="small text-muted">
                            A conclusão geral permanece bloqueada porque o
                            romaneio não possui uma entrega vinculada.
                        </div>
                    <?php else: ?>
                        <div class="fw-bold text-success">
                            Triagens concluídas.
                        </div>
                        <div class="small text-muted">
                            O saldo sem ocorrência será consolidado como
                            entregue. As ocorrências permanecerão registradas
                            no histórico e seguirão abertas para tratativa
                            administrativa. Nenhum estoque será movimentado
                            nesta etapa.
                        </div>
                    <?php endif; ?>
                </div>

                <div class="col-xl-4 text-xl-end">
                    <form
                        method="POST"
                        action="<?php echo e(route(
                                'romaneios.ocorrencias.triagem.finalizar',
                                $romaneio
                            )); ?>"
                        id="formFinalizacaoGeral"
                    >
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('PATCH'); ?>

                        <button
                            type="submit"
                            class="btn btn-success"
                            data-bs-toggle="tooltip"
                            data-bs-placement="left"
                            title="Consolidar o saldo normal como entregue e encaminhar o romaneio para a prestação de contas."
                            <?php if(
                                $produtosComProblema === 0
                                || ! $todasTriagensConcluidas
                                || ! $romaneio->entrega_id
                            ): echo 'disabled'; endif; ?>
                        >
                            <i class="bi bi-check2-circle me-1"></i>
                            Consolidar retorno e encaminhar ocorrências
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const materiais = <?php echo json_encode($materiaisJson, 15, 512) ?>;
    const opcoes = <?php echo json_encode($opcoesFormulario, 15, 512) ?>;
    const materialAntigo = <?php echo json_encode(old('romaneio_item_id'), 15, 512) ?>;
    const avaliacoesAntigas = <?php echo json_encode(old('avaliacoes', []), 512) ?>;
    const tipoAntigo = <?php echo json_encode(old('tipo_resultado'), 15, 512) ?>;

    const form = document.getElementById('formTriagem');
    const romaneioItemId = document.getElementById('romaneioItemId');
    const produtoSelecionado = document.getElementById(
        'produtoSelecionado'
    );
    const loteSelecionado = document.getElementById('loteSelecionado');
    const quantidadeSaidaSelecionada = document.getElementById(
        'quantidadeSaidaSelecionada'
    );
    const saldoSelecionado = document.getElementById('saldoSelecionado');
    const tipoResultado = document.getElementById('tipoResultado');
    const quantidadeAfetada = document.getElementById(
        'quantidadeAfetada'
    );
    const saldoAposOcorrencia = document.getElementById(
        'saldoAposOcorrencia'
    );
    const alertaLote = document.getElementById('alertaLote');
    const blocoAvaliacaoFisica = document.getElementById(
        'blocoAvaliacaoFisica'
    );
    const avisoExtravio = document.getElementById('avisoExtravio');
    const avaliacoesBody = document.getElementById('avaliacoesBody');
    const quantidadeOcorrencia = document.getElementById(
        'quantidadeOcorrencia'
    );
    const quantidadeAvaliada = document.getElementById(
        'quantidadeAvaliada'
    );
    const statusConferencia = document.getElementById(
        'statusConferencia'
    );
    const btnAdicionar = document.getElementById(
        'btnAdicionarAvaliacao'
    );
    const btnSalvar = document.getElementById('btnSalvarAvaliacao');
    const btnFechar = document.getElementById('btnFecharAvaliacao');
    const btnCancelar = document.getElementById(
        'btnCancelarAvaliacao'
    );
    const inputEvidencias = document.getElementById('evidencias');
    const arquivosSelecionados = document.getElementById(
        'arquivosSelecionados'
    );
    const evidenciasExistentes = document.getElementById(
        'evidenciasExistentes'
    );
    const orientacaoSalvar = document.getElementById(
        'orientacaoSalvar'
    );

    let materialAtual = null;
    let indiceLinha = 0;
    let urlsPreviaEvidencias = [];
    let evidenciasSelecionadas = [];

    const tiposFisicos = ['Devolucao', 'Recusa', 'Avaria'];
    const passoQuantidade = 0.50;
    const passoQuantidadeMilesimos = 500;

    const rotulos = {
        Intacta: 'Intacta',
        Rasgada: 'Rasgada',
        Aberta: 'Aberta',
        Furada: 'Furada',
        Amassada: 'Amassada',
        Quebrada: 'Quebrada',
        Lacre_violado: 'Lacre violado',
        Sem_embalagem: 'Sem embalagem',
        Nao_se_aplica: 'Não se aplica',
        Preservado: 'Preservado',
        Parcial: 'Parcial',
        Vazando: 'Vazando',
        Derramado: 'Derramado',
        Espalhado: 'Espalhado',
        Endurecido: 'Endurecido',
        Molhado: 'Molhado',
        Misturado: 'Misturado',
        Contaminado: 'Contaminado',
        Ausente: 'Ausente',
        Integro: 'Íntegro',
        Dano_leve: 'Dano leve',
        Dano_parcial: 'Dano parcial',
        Deformado: 'Deformado',
        Incompleto: 'Incompleto',
        Perda_total: 'Perda total',
        Avaliacao_inconclusiva: 'Avaliação inconclusiva',
        Dentro_validade: 'Dentro da validade',
        Proximo_vencimento: 'Próximo do vencimento',
        Vencido: 'Vencido',
        Data_ilegivel: 'Data ilegível',
        Sem_identificacao: 'Sem identificação',
        Uso_normal: 'Uso normal',
        Reembalagem: 'Reembalagem',
        Troca_embalagem: 'Trocar embalagem',
        Reparo_embalagem: 'Reparar embalagem',
        Venda_granel: 'Venda a granel',
        Venda_com_avaria: 'Venda com avaria',
        Uso_interno: 'Uso interno',
        Aproveitamento_parcial: 'Aproveitamento parcial',
        Retorno_fornecedor: 'Retorno ao fornecedor',
        Reciclagem: 'Reciclagem',
        Sem_reaproveitamento: 'Sem reaproveitamento',
        Sem_movimentacao: 'Sem movimentação',
        Quarentena: 'Quarentena',
        Reintegracao: 'Devolver ao estoque',
        Perda: 'Descarte / perda',
        Reposicao: 'Reposição ao cliente'
    };

    function formatarQuantidade(valor) {
        return Number(valor || 0).toLocaleString('pt-BR', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 2
        });
    }

    function formatarQuantidadeInput(valor) {
        const numero = Number(valor);

        if (! Number.isFinite(numero)) {
            return '';
        }

        return Number.isInteger(numero)
            ? String(numero)
            : numero.toFixed(2).replace(/0+$/, '').replace(/\.$/, '');
    }

    function quantidadeRespeitaPasso(valorMilesimos) {
        return valorMilesimos > 0
            && valorMilesimos % passoQuantidadeMilesimos === 0;
    }

    function calcularLimiteOcorrencia(material) {
        const limiteBruto = Math.max(
            0,
            Math.min(
                Number(material?.quantidade_saida || 0),
                Number(material?.saldo_normal || 0)
            )
        );
        const limiteMilesimos = Math.floor(
            Math.round(limiteBruto * 1000)
            / passoQuantidadeMilesimos
        ) * passoQuantidadeMilesimos;

        return limiteMilesimos / 1000;
    }

    function escaparHtml(valor) {
        const div = document.createElement('div');
        div.textContent = valor ?? '';
        return div.innerHTML;
    }

    function opcoesSelect(valores, selecionado, placeholder = true) {
        let html = placeholder
            ? '<option value="">Selecione</option>'
            : '';

        valores.forEach(valor => {
            html += `<option value="${escaparHtml(valor)}" ${
                valor === selecionado ? 'selected' : ''
            }>${escaparHtml(
                rotulos[valor] ?? String(valor).replaceAll('_', ' ')
            )}</option>`;
        });

        return html;
    }

    function calcularValidade(material) {
        if (! material?.controla_validade) {
            return 'Nao_se_aplica';
        }

        const referencia = material.validade_lote
            || material.validade_produto;

        if (! referencia) {
            return 'Sem_identificacao';
        }

        const hoje = new Date();
        hoje.setHours(0, 0, 0, 0);

        const validade = new Date(`${referencia}T00:00:00`);
        const limite = new Date(hoje);
        limite.setDate(limite.getDate() + 30);

        if (validade < hoje) {
            return 'Vencido';
        }

        if (validade <= limite) {
            return 'Proximo_vencimento';
        }

        return 'Dentro_validade';
    }

    function criarLinha(dados = {}) {
        if (! materialAtual) {
            return;
        }

        const indice = indiceLinha++;
        const limiteInicial = calcularLimiteOcorrencia(
            materialAtual
        );
        const validadeInformada = dados.validade_status
            || calcularValidade(materialAtual);
        const validadeAutomatica = ! [
            'Data_ilegivel',
            'Sem_identificacao'
        ].includes(validadeInformada);

        const tr = document.createElement('tr');
        tr.dataset.indice = indice;

        tr.innerHTML = `
            <td>
                <input
                    type="number"
                    name="avaliacoes[${indice}][quantidade]"
                    class="form-control quantidade-avaliacao"
                    min="0.50"
                    max="${formatarQuantidadeInput(limiteInicial)}"
                    step="0.50"
                    value="${escaparHtml(
                        dados.quantidade === ''
                        || dados.quantidade === null
                        || dados.quantidade === undefined
                            ? ''
                            : formatarQuantidadeInput(dados.quantidade)
                    )}"
                    required
                >
            </td>
            <td>
                <select
                    name="avaliacoes[${indice}][embalagem]"
                    class="form-select"
                    required
                >
                    ${opcoesSelect(
                        opcoes.embalagens,
                        dados.embalagem
                    )}
                </select>
            </td>
            <td>
                <select
                    name="avaliacoes[${indice}][conteudo]"
                    class="form-select"
                    required
                >
                    ${opcoesSelect(
                        opcoes.conteudos,
                        dados.conteudo
                    )}
                </select>
            </td>
            <td>
                <select
                    name="avaliacoes[${indice}][integridade]"
                    class="form-select"
                    required
                >
                    ${opcoesSelect(
                        opcoes.integridades,
                        dados.integridade
                    )}
                </select>
            </td>
            <td>
                <select
                    name="avaliacoes[${indice}][validade_status]"
                    class="form-select validade-status ${
                        validadeAutomatica
                            ? 'validade-automatica'
                            : ''
                    }"
                    required
                >
                    ${opcoesSelect(
                        opcoes.statusValidade,
                        validadeInformada,
                        false
                    )}
                </select>
                <button
                    type="button"
                    class="btn btn-link btn-sm p-0 mt-1 alterar-validade"
                    data-bs-toggle="tooltip"
                    title="Informar data ilegível ou ausência de identificação."
                >
                    Informar problema na identificação
                </button>
            </td>
            <td>
                <select
                    name="avaliacoes[${indice}][reaproveitamento]"
                    class="form-select"
                    required
                >
                    ${opcoesSelect(
                        opcoes.reaproveitamentos,
                        dados.reaproveitamento
                    )}
                </select>
            </td>
            <td>
                <select
                    name="avaliacoes[${indice}][destino_sugerido]"
                    class="form-select"
                    required
                >
                    ${opcoesSelect(
                        opcoes.destinos,
                        dados.destino_sugerido
                    )}
                </select>
                <input
                    type="text"
                    name="avaliacoes[${indice}][observacao]"
                    class="form-control mt-1"
                    maxlength="1000"
                    value="${escaparHtml(dados.observacao ?? '')}"
                    placeholder="Observação opcional"
                >
            </td>
            <td class="text-center">
                <button
                    type="button"
                    class="btn btn-sm btn-outline-danger remover-avaliacao"
                    data-bs-toggle="tooltip"
                    title="Excluir esta linha de avaliação."
                >
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        `;

        avaliacoesBody.appendChild(tr);

        tr.querySelectorAll('input, select').forEach(campo => {
            campo.addEventListener('input', atualizarEstado);
            campo.addEventListener('change', atualizarEstado);
        });

        tr.querySelector('.remover-avaliacao')
            .addEventListener('click', function () {
                tr.remove();

                if (! avaliacoesBody.children.length) {
                    criarLinha({
                        quantidade: ''
                    });
                }

                atualizarEstado();
            });

        tr.querySelector('.alterar-validade')
            .addEventListener('click', function () {
                const select = tr.querySelector('.validade-status');
                const escolha = window.prompt(
                    'Digite 1 para Data ilegível ou 2 para Sem identificação.'
                );

                if (escolha === '1') {
                    select.value = 'Data_ilegivel';
                    select.classList.remove('validade-automatica');
                } else if (escolha === '2') {
                    select.value = 'Sem_identificacao';
                    select.classList.remove('validade-automatica');
                } else {
                    select.value = calcularValidade(materialAtual);
                    select.classList.add('validade-automatica');
                }

                atualizarEstado();
            });

        tr.querySelectorAll('[data-bs-toggle="tooltip"]')
            .forEach(elemento => {
                if (window.bootstrap?.Tooltip) {
                    bootstrap.Tooltip.getOrCreateInstance(elemento);
                }
            });
    }

    function habilitarCamposFisicos(habilitar) {
        blocoAvaliacaoFisica
            .querySelectorAll('input, select, button')
            .forEach(campo => {
                campo.disabled = ! habilitar;
            });

        btnAdicionar.disabled = ! habilitar;
    }

    function prepararAvaliacaoFisica() {
        const fisico = tiposFisicos.includes(tipoResultado.value);

        blocoAvaliacaoFisica.classList.toggle('d-none', ! fisico);
        avisoExtravio.classList.toggle(
            'd-none',
            tipoResultado.value !== 'Extravio'
        );

        habilitarCamposFisicos(fisico);

        if (fisico && ! avaliacoesBody.children.length) {
            const dadosIniciais = avaliacoesAntigas.length
                ? avaliacoesAntigas
                : [{
                    quantidade: ''
                }];

            dadosIniciais.forEach(criarLinha);
        }
    }

    function totalAvaliadoMilesimos() {
        return Array.from(
            avaliacoesBody.querySelectorAll('.quantidade-avaliacao')
        ).reduce((soma, input) => {
            return soma
                + Math.round(Number(input.value || 0) * 1000);
        }, 0);
    }

    function atualizarEstado() {
        if (! materialAtual) {
            btnSalvar.disabled = true;
            return;
        }

        prepararAvaliacaoFisica();

        const tipo = tipoResultado.value;
        const fisico = tiposFisicos.includes(tipo);
        const extravio = tipo === 'Extravio';
        const saldo = Number(materialAtual.saldo_normal || 0);
        const limiteOcorrencia = calcularLimiteOcorrencia(
            materialAtual
        );
        const limiteMilesimos = Math.round(
            limiteOcorrencia * 1000
        );

        quantidadeAfetada.readOnly = fisico;
        quantidadeAfetada.classList.toggle('bg-light', fisico);
        quantidadeAfetada.min =
            passoQuantidade.toFixed(2);
        quantidadeAfetada.step =
            passoQuantidade.toFixed(2);
        quantidadeAfetada.max =
            formatarQuantidadeInput(limiteOcorrencia);

        let quantidade = 0;
        let totalAvaliado = 0;
        let linhasAvaliadasValidas = true;

        if (fisico) {
            const inputsQuantidade = Array.from(
                avaliacoesBody.querySelectorAll(
                    '.quantidade-avaliacao'
                )
            );

            inputsQuantidade.forEach(input => {
                let valor = Number(input.value || 0);

                if (! Number.isFinite(valor) || valor < 0) {
                    input.value = '';
                    valor = 0;
                }

                let valorMilesimos = Math.round(valor * 1000);
                const restanteMilesimos = Math.max(
                    0,
                    limiteMilesimos - totalAvaliado
                );

                input.min = passoQuantidade.toFixed(2);
                input.step = passoQuantidade.toFixed(2);
                input.max = formatarQuantidadeInput(
                    restanteMilesimos / 1000
                );

                if (valorMilesimos > restanteMilesimos) {
                    valorMilesimos = restanteMilesimos;
                    input.value = restanteMilesimos > 0
                        ? formatarQuantidadeInput(
                            restanteMilesimos / 1000
                        )
                        : '';
                }

                if (! quantidadeRespeitaPasso(valorMilesimos)) {
                    linhasAvaliadasValidas = false;
                }

                totalAvaliado += valorMilesimos;
            });

            quantidade = totalAvaliado / 1000;
            quantidadeAfetada.value = quantidade > 0
                ? formatarQuantidadeInput(quantidade)
                : '';

            btnAdicionar.disabled =
                limiteMilesimos <= 0
                || totalAvaliado >= limiteMilesimos
                || ! linhasAvaliadasValidas;
        } else {
            quantidade = Number(quantidadeAfetada.value || 0);

            if (! Number.isFinite(quantidade) || quantidade < 0) {
                quantidadeAfetada.value = '';
                quantidade = 0;
            }

            if (
                Math.round(quantidade * 1000)
                > limiteMilesimos
            ) {
                quantidadeAfetada.value =
                    formatarQuantidadeInput(limiteOcorrencia);
                quantidade = limiteOcorrencia;
            } else if (
                quantidadeRespeitaPasso(
                    Math.round(quantidade * 1000)
                )
            ) {
                quantidadeAfetada.value =
                    formatarQuantidadeInput(quantidade);
            }

            btnAdicionar.disabled = true;
        }

        const quantidadeValida =
            quantidade > 0
            && Math.round(quantidade * 1000)
                <= Math.round(limiteOcorrencia * 1000)
            && quantidadeRespeitaPasso(
                Math.round(quantidade * 1000)
            );
        const loteValido = ! fisico || Boolean(materialAtual.lote_id);

        quantidadeOcorrencia.textContent =
            formatarQuantidade(quantidade);
        saldoAposOcorrencia.textContent = formatarQuantidade(
            Math.max(0, saldo - quantidade)
        );

        alertaLote.classList.toggle(
            'd-none',
            ! fisico || Boolean(materialAtual.lote_id)
        );

        let avaliacaoValida = true;

        if (fisico) {
            quantidadeAvaliada.textContent =
                formatarQuantidade(totalAvaliado / 1000);

            avaliacaoValida =
                avaliacoesBody.children.length > 0
                && totalAvaliado > 0
                && totalAvaliado <= limiteMilesimos
                && linhasAvaliadasValidas;

            if (avaliacaoValida && totalAvaliado === limiteMilesimos) {
                statusConferencia.textContent =
                    'Saída total distribuída';
            } else if (avaliacaoValida) {
                statusConferencia.textContent =
                    'Quantidade conferida';
            } else {
                statusConferencia.textContent =
                    'Informe as quantidades';
            }

            statusConferencia.className = avaliacaoValida
                ? 'badge text-bg-success fs-6'
                : 'badge text-bg-warning fs-6';
        } else {
            quantidadeAvaliada.textContent = '0';
        }

        const formularioValido =
            Boolean(tipo)
            && quantidadeValida
            && loteValido
            && (
                extravio
                || (fisico && avaliacaoValida)
            );

        btnSalvar.disabled = ! formularioValido;

        if (! tipo) {
            orientacaoSalvar.textContent =
                'Selecione o tipo do problema.';
        } else if (! quantidadeValida) {
            orientacaoSalvar.textContent =
                'Informe uma quantidade válida em intervalos de 0,50, dentro do saldo disponível.';
        } else if (! loteValido) {
            orientacaoSalvar.textContent =
                'O processamento físico está bloqueado por lote ausente.';
        } else if (fisico && ! avaliacaoValida) {
            orientacaoSalvar.textContent =
                'Informe uma quantidade maior que zero em cada avaliação.';
        } else {
            orientacaoSalvar.textContent =
                'Os dados estão prontos para salvar este material.';
        }
    }

    function atualizarEvidencias() {
        const anexos = materialAtual?.anexos ?? [];

        if (! anexos.length) {
            evidenciasExistentes.innerHTML = `
                <div class="text-muted">
                    Nenhuma evidência registrada para este material.
                </div>
            `;
            return;
        }

        evidenciasExistentes.innerHTML = `
            <div class="row g-2">
                ${anexos.map(anexo => {
                    const imagem = String(anexo.mime_type || '')
                        .startsWith('image/');

                    return `
                        <div class="col-6 col-md-4">
                            <a
                                href="${escaparHtml(anexo.url)}"
                                target="_blank"
                                class="evidencia-miniatura text-decoration-none"
                            >
                                ${imagem
                                    ? `<img
                                        src="${escaparHtml(anexo.url)}"
                                        alt="Evidência"
                                    >`
                                    : `<span class="text-center">
                                        <i class="bi bi-file-earmark-pdf fs-2 text-danger d-block"></i>
                                        Abrir documento
                                    </span>`
                                }
                            </a>
                        </div>
                    `;
                }).join('')}
            </div>
        `;
    }

    function selecionarMaterial(id, restaurarAntigos = false) {
        materialAtual = materiais[String(id)];

        if (! materialAtual) {
            return;
        }

        document.querySelectorAll('[data-material-id]')
            .forEach(linha => linha.classList.remove('selecionado'));

        document.getElementById(`linha-material-${id}`)
            ?.classList.add('selecionado');

        romaneioItemId.value = id;
        produtoSelecionado.textContent = materialAtual.produto;
        loteSelecionado.textContent = materialAtual.lote
            || (
                materialAtual.lote_id
                    ? `Lote #${materialAtual.lote_id}`
                    : 'Não identificado'
            );
        quantidadeSaidaSelecionada.textContent = formatarQuantidade(
            materialAtual.quantidade_saida
        );
        saldoSelecionado.textContent = formatarQuantidade(
            materialAtual.saldo_normal
        );

        if (! restaurarAntigos) {
            tipoResultado.value = '';
            quantidadeAfetada.value = '';
            document.getElementById('justificativaTriagem').value = '';
        }

        avaliacoesBody.innerHTML = '';
        indiceLinha = 0;
        limparEvidenciasSelecionadas();

        form.classList.remove('d-none');
        atualizarEvidencias();
        atualizarEstado();
        form.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });
    }

    function fecharAvaliacao() {
        document.querySelectorAll('[data-material-id]')
            .forEach(linha => linha.classList.remove('selecionado'));

        materialAtual = null;
        romaneioItemId.value = '';
        tipoResultado.value = '';
        quantidadeAfetada.value = '';
        avaliacoesBody.innerHTML = '';
        indiceLinha = 0;
        limparEvidenciasSelecionadas();
        form.classList.add('d-none');
        btnSalvar.disabled = true;
    }

    function limparPreviewsEvidencias() {
        urlsPreviaEvidencias.forEach(url => URL.revokeObjectURL(url));
        urlsPreviaEvidencias = [];
        arquivosSelecionados.innerHTML = '';
    }

    function sincronizarInputEvidencias() {
        const transferencia = new DataTransfer();

        evidenciasSelecionadas.forEach(arquivo => {
            transferencia.items.add(arquivo);
        });

        inputEvidencias.files = transferencia.files;
    }

    function limparEvidenciasSelecionadas() {
        evidenciasSelecionadas = [];
        inputEvidencias.value = '';
        limparPreviewsEvidencias();
    }

    function chaveArquivo(arquivo) {
        return [
            arquivo.name,
            arquivo.size,
            arquivo.lastModified,
            arquivo.type,
        ].join('|');
    }

    function removerEvidenciaSelecionada(indiceRemover) {
        evidenciasSelecionadas = evidenciasSelecionadas.filter(
            (arquivo, indice) => indice !== indiceRemover
        );

        sincronizarInputEvidencias();
        renderizarArquivosSelecionados();
    }

    function atualizarArquivosSelecionados() {
        const novosArquivos = Array.from(
            inputEvidencias.files ?? []
        );
        const arquivosConhecidos = new Set(
            evidenciasSelecionadas.map(chaveArquivo)
        );
        const arquivosAcumulados = [
            ...evidenciasSelecionadas,
        ];

        novosArquivos.forEach(arquivo => {
            const chave = chaveArquivo(arquivo);

            if (! arquivosConhecidos.has(chave)) {
                arquivosAcumulados.push(arquivo);
                arquivosConhecidos.add(chave);
            }
        });

        if (arquivosAcumulados.length > 4) {
            window.alert(
                'É permitido adicionar no máximo 4 evidências.'
            );
            sincronizarInputEvidencias();
            renderizarArquivosSelecionados();
            return;
        }

        const possuiArquivoMaior = arquivosAcumulados.some(
            arquivo => arquivo.size > 10 * 1024 * 1024
        );

        if (possuiArquivoMaior) {
            window.alert(
                'Cada evidência deve possuir no máximo 10 MB.'
            );
            sincronizarInputEvidencias();
            renderizarArquivosSelecionados();
            return;
        }

        evidenciasSelecionadas = arquivosAcumulados;
        sincronizarInputEvidencias();
        renderizarArquivosSelecionados();
    }

    function renderizarArquivosSelecionados() {
        limparPreviewsEvidencias();

        arquivosSelecionados.innerHTML = evidenciasSelecionadas.map((
            arquivo,
            indice
        ) => {
            const tamanhoMb = (
                arquivo.size
                / 1024
                / 1024
            ).toFixed(2);
            const imagem = String(arquivo.type || '')
                .startsWith('image/');
            let preview;

            if (imagem) {
                const url = URL.createObjectURL(arquivo);
                urlsPreviaEvidencias.push(url);

                preview = `
                    <img
                        src="${escaparHtml(url)}"
                        alt="Pré-visualização de ${escaparHtml(
                            arquivo.name
                        )}"
                    >
                `;
            } else {
                preview = `
                    <span class="text-center text-danger">
                        <i class="bi bi-file-earmark-pdf fs-1 d-block"></i>
                        PDF
                    </span>
                `;
            }

            return `
                <div class="arquivo-selecionado">
                    <div class="arquivo-preview">
                        ${preview}
                    </div>
                    <button
                        type="button"
                        class="btn btn-danger btn-sm remover-evidencia-selecionada"
                        data-indice="${indice}"
                        title="Remover esta evidência"
                        aria-label="Remover ${escaparHtml(arquivo.name)}"
                    >
                        <i class="bi bi-trash"></i>
                    </button>
                    <div class="arquivo-dados">
                        <div
                            class="text-truncate small fw-semibold"
                            title="${escaparHtml(arquivo.name)}"
                        >
                            ${escaparHtml(arquivo.name)}
                        </div>
                        <small class="text-muted">
                            ${tamanhoMb} MB
                        </small>
                    </div>
                </div>
            `;
        }).join('');
    }

    document.querySelectorAll('.btn-informar-problema')
        .forEach(botao => {
            botao.addEventListener('click', function () {
                selecionarMaterial(this.dataset.materialId);
            });
        });

    tipoResultado.addEventListener('change', atualizarEstado);
    quantidadeAfetada.addEventListener('input', atualizarEstado);

    btnAdicionar.addEventListener('click', function () {
        if (! materialAtual) {
            return;
        }

        const limiteOcorrencia = calcularLimiteOcorrencia(
            materialAtual
        );
        const avaliada = totalAvaliadoMilesimos() / 1000;
        const restante = Math.max(
            0,
            limiteOcorrencia - avaliada
        );

        if (restante <= 0) {
            atualizarEstado();
            return;
        }

        criarLinha({
            quantidade: formatarQuantidadeInput(restante)
        });
        atualizarEstado();
    });

    btnFechar.addEventListener('click', fecharAvaliacao);
    btnCancelar.addEventListener('click', fecharAvaliacao);
    inputEvidencias.addEventListener(
        'change',
        atualizarArquivosSelecionados
    );
    arquivosSelecionados.addEventListener('click', function (event) {
        const botao = event.target.closest(
            '.remover-evidencia-selecionada'
        );

        if (! botao) {
            return;
        }

        removerEvidenciaSelecionada(
            Number(botao.dataset.indice)
        );
    });

    form.addEventListener('input', atualizarEstado);
    form.addEventListener('change', atualizarEstado);

    form.addEventListener('submit', function (event) {
        atualizarEstado();

        if (btnSalvar.disabled || ! form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
            form.classList.add('was-validated');
            form.reportValidity();
        }
    });

    const formFinalizacao = document.getElementById(
        'formFinalizacaoGeral'
    );

    formFinalizacao?.addEventListener('submit', function (event) {
        const confirmado = window.confirm(
            'Consolidar o retorno? O saldo sem ocorrência será considerado entregue, o romaneio seguirá para a prestação de contas e as ocorrências permanecerão abertas no histórico. Nenhum estoque será movimentado agora.'
        );

        if (! confirmado) {
            event.preventDefault();
        }
    });

    document.querySelectorAll('[data-bs-toggle="tooltip"]')
        .forEach(elemento => {
            if (window.bootstrap?.Tooltip) {
                bootstrap.Tooltip.getOrCreateInstance(elemento);
            }
        });

    if (materialAntigo && materiais[String(materialAntigo)]) {
        selecionarMaterial(materialAntigo, true);
        tipoResultado.value = tipoAntigo || '';
        quantidadeAfetada.value = <?php echo json_encode(
            old('quantidade_afetada')
        , 15, 512) ?>;
        atualizarEstado();
    }
});
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/romaneios/ocorrencias/triagem.blade.php ENDPATH**/ ?>