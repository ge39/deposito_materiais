<?php

namespace App\Services\Entregas;

use App\Models\Entrega;
use App\Models\EntregaItem;
use App\Models\Funcionario;
use App\Models\Orcamento;
use App\Models\Romaneio;
use App\Models\RomaneioItem;
use App\Models\Veiculo;
use App\Models\Venda;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class EntregaService
{
    private array $statusValidos = [
        'Pendente_pagamento',
        'Aguardando_faturamento',
        'Aguardando_separacao',
        'Em_preparacao',
        'Pronta_para_carregamento',
        'Carregada',
        'Liberada',
        'Em_rota',
        'No_destino',
        'Entregue',
        'Entregue_parcial',
        'Nao_entregue',
        'Recusada',
        'Reagendada',
        'Devolvida',
        'Cancelada',
    ];

    private array $statusFinais = [
        'Entregue',
        'Devolvida',
        'Cancelada',
    ];

    public function criar(array $dados): Entrega
    {
        return DB::transaction(function () use ($dados) {
            $status = $dados['status'] ?? 'Aguardando_separacao';

            $this->validarStatus($status);

            if (empty($dados['data_prevista'])) {
                throw ValidationException::withMessages([
                    'data_prevista' => 'A data prevista da entrega é obrigatória.',
                ]);
            }

            if (empty($dados['itens'])) {
                throw ValidationException::withMessages([
                    'itens' => 'A entrega precisa possuir pelo menos um item.',
                ]);
            }

            if (! empty($dados['venda_id'])) {
                $entregaExistente = Entrega::query()
                    ->where('venda_id', $dados['venda_id'])
                    ->lockForUpdate()
                    ->first();

                if ($entregaExistente) {
                    return $entregaExistente->load('itens');
                }
            }

            $entrega = Entrega::create([
                'orcamento_id' => $dados['orcamento_id'] ?? null,
                'venda_id' => $dados['venda_id'] ?? null,
                'codigo_entrega' => $dados['codigo_entrega'] ?? $this->gerarCodigo(),
                'data_prevista' => $dados['data_prevista'],
                'data_prevista_entrega' => $dados['data_prevista_entrega'] ?? null,
                'periodo_entrega' => $dados['periodo_entrega'] ?? null,
                'observacao_entrega' => $dados['observacao_entrega'] ?? null,
                'data_realizada' => null,
                'status' => $status,
                'cobrar_frete' => $dados['cobrar_frete'] ?? 0,
                'valor_frete' => $dados['valor_frete'] ?? 0,
                'tipo_entrega' => $dados['tipo_entrega'] ?? 'entrega',
                'usar_endereco_cliente' => $dados['usar_endereco_cliente'] ?? 1,
                'endereco_entrega' => $dados['endereco_entrega'] ?? null,
                'responsavel_recebimento' => $dados['responsavel_recebimento'] ?? null,
                'telefone_recebimento' => $dados['telefone_recebimento'] ?? null,
                'motorista_id' => $dados['motorista_id'] ?? null,
                'veiculo_id' => $dados['veiculo_id'] ?? null,
                'ordem_rota' => $dados['ordem_rota'] ?? null,
                'observacao' => $dados['observacao'] ?? null,
            ]);

            foreach ($dados['itens'] as $item) {
                EntregaItem::create([
                    'entrega_id' => $entrega->id,
                    'venda_item_id' => $item['venda_item_id'] ?? null,
                    'item_orcamento_id' => $item['item_orcamento_id'] ?? null,
                    'quantidade_prevista' => (float) $item['quantidade_prevista'],
                    'quantidade_entregue' => 0,
                    'quantidade_recusada' => 0,
                    'quantidade_devolvida' => 0,
                    'quantidade_avariada' => 0,
                    'status' => 'Pendente',
                    'observacao' => $item['observacao'] ?? null,
                ]);
            }

            return $entrega->fresh('itens');
        });
    }

    public function criarEntregaFracionada(
        Entrega $entregaOrigem,
        array $itens,
        array $dados = []
    ): Entrega {
        return DB::transaction(function () use (
            $entregaOrigem,
            $itens,
            $dados
        ) {
            $entregaOrigem = Entrega::query()
                ->with('itens')
                ->lockForUpdate()
                ->findOrFail($entregaOrigem->id);

            if (empty($itens)) {
                throw ValidationException::withMessages([
                    'itens' =>
                        'A entrega fracionada precisa possuir pelo menos um item.',
                ]);
            }

            $itensInformados = collect($itens)
                ->map(function ($item) {
                    if (! is_array($item)) {
                        throw ValidationException::withMessages([
                            'itens' =>
                                'Os itens da entrega fracionada são inválidos.',
                        ]);
                    }

                    $itemOrigemId = (int) (
                        $item['entrega_item_origem_id']
                        ?? 0
                    );

                    $quantidade = round(
                        (float) ($item['quantidade'] ?? 0),
                        2
                    );

                    if (
                        $itemOrigemId <= 0
                        || $quantidade <= 0
                    ) {
                        throw ValidationException::withMessages([
                            'itens' =>
                                'Informe um item de origem e uma quantidade válida para o fracionamento.',
                        ]);
                    }

                    return [
                        'entrega_item_origem_id' =>
                            $itemOrigemId,

                        'quantidade' =>
                            $quantidade,
                    ];
                })
                ->values();

            if (
                $itensInformados
                    ->pluck('entrega_item_origem_id')
                    ->unique()
                    ->count()
                !== $itensInformados->count()
            ) {
                throw ValidationException::withMessages([
                    'itens' =>
                        'O mesmo item de origem foi informado mais de uma vez no fracionamento.',
                ]);
            }

            $itensOrigemPorId = $entregaOrigem
                ->itens
                ->whereIn(
                    'id',
                    $itensInformados->pluck(
                        'entrega_item_origem_id'
                    )
                )
                ->keyBy('id');

            if (
                $itensOrigemPorId->count()
                !== $itensInformados->count()
            ) {
                throw ValidationException::withMessages([
                    'itens' =>
                        'Um ou mais itens informados não pertencem à entrega de origem.',
                ]);
            }

            foreach ($itensInformados as $itemInformado) {
                $itemOrigem = $itensOrigemPorId->get(
                    $itemInformado['entrega_item_origem_id']
                );

                if (
                    $itemInformado['quantidade']
                    - (float) $itemOrigem->quantidade_prevista
                    >= 0.001
                ) {
                    throw ValidationException::withMessages([
                        'itens' =>
                            "A quantidade fracionada do item #{$itemOrigem->id} ultrapassa a quantidade prevista.",
                    ]);
                }
            }

            $entregasFilhasAtivas = Entrega::query()
                ->with('itens')
                ->where(
                    'entrega_origem_id',
                    $entregaOrigem->id
                )
                ->where(
                    'status',
                    '<>',
                    'Cancelada'
                )
                ->lockForUpdate()
                ->get();

            if ($entregasFilhasAtivas->count() > 1) {
                throw ValidationException::withMessages([
                    'entrega' =>
                        "A entrega {$entregaOrigem->codigo_entrega} possui mais de uma entrega filha ativa.",
                ]);
            }

            $entregaFilhaExistente =
                $entregasFilhasAtivas->first();

            if ($entregaFilhaExistente) {
                $itensFilhosPorOrigem = $entregaFilhaExistente
                    ->itens
                    ->keyBy('entrega_item_origem_id');

                if (
                    $itensFilhosPorOrigem->count()
                    !== $itensInformados->count()
                ) {
                    throw ValidationException::withMessages([
                        'entrega' =>
                            "A entrega complementar {$entregaFilhaExistente->codigo_entrega} já existe com uma composição diferente.",
                    ]);
                }

                foreach ($itensInformados as $itemInformado) {
                    $itemFilho = $itensFilhosPorOrigem->get(
                        $itemInformado[
                            'entrega_item_origem_id'
                        ]
                    );

                    if (
                        ! $itemFilho
                        || abs(
                            (float) $itemFilho
                                ->quantidade_prevista
                            - $itemInformado['quantidade']
                        ) >= 0.001
                    ) {
                        throw ValidationException::withMessages([
                            'entrega' =>
                                "O saldo atual é diferente da entrega complementar {$entregaFilhaExistente->codigo_entrega} já criada.",
                        ]);
                    }
                }

                return $entregaFilhaExistente;
            }

            $entregaPrincipalId = (int) (
                $entregaOrigem->entrega_principal_id
                ?: $entregaOrigem->id
            );

            $dataPrevista =
                $dados['data_prevista']
                ?? $entregaOrigem->data_prevista_entrega
                ?? $entregaOrigem->data_prevista
                ?? now()->toDateString();

            $observacaoInformada = trim(
                (string) ($dados['observacao'] ?? '')
            );

            $observacaoFracionamento =
                'Entrega complementar gerada pelo fracionamento da entrega '
                . $entregaOrigem->codigo_entrega
                . '.';

            $observacao = $observacaoInformada !== ''
                ? $observacaoFracionamento
                    . PHP_EOL
                    . $observacaoInformada
                : $observacaoFracionamento;

            $entregaFilha = Entrega::create([
                'entrega_origem_id' =>
                    $entregaOrigem->id,

                'entrega_principal_id' =>
                    $entregaPrincipalId,

                'orcamento_id' =>
                    $entregaOrigem->orcamento_id,

                'venda_id' =>
                    $entregaOrigem->venda_id,

                'codigo_entrega' =>
                    $this->gerarCodigo(),

                'data_prevista' =>
                    $dataPrevista,

                'data_prevista_entrega' =>
                    $dados['data_prevista_entrega']
                    ?? $dataPrevista,

                'periodo_entrega' =>
                    $dados['periodo_entrega']
                    ?? $entregaOrigem->periodo_entrega,

                'observacao_entrega' =>
                    $observacao,

                'data_realizada' =>
                    null,

                'status' =>
                    'Aguardando_separacao',

                /*
                * O fracionamento não cria uma nova cobrança
                * automaticamente para a mesma venda.
                */
                'cobrar_frete' =>
                    $dados['cobrar_frete'] ?? false,

                'valor_frete' =>
                    $dados['valor_frete'] ?? 0,

                'tipo_entrega' =>
                    $entregaOrigem->tipo_entrega
                    ?? 'entrega',

                'usar_endereco_cliente' =>
                    $entregaOrigem->usar_endereco_cliente,

                'endereco_entrega' =>
                    $entregaOrigem->endereco_entrega,

                'responsavel_recebimento' =>
                    $entregaOrigem->responsavel_recebimento,

                'telefone_recebimento' =>
                    $entregaOrigem->telefone_recebimento,

                'motorista_id' =>
                    $dados['motorista_id'] ?? null,

                'veiculo_id' =>
                    $dados['veiculo_id'] ?? null,

                'ordem_rota' =>
                    null,

                'observacao' =>
                    $observacao,
            ]);

            foreach ($itensInformados as $itemInformado) {
                $itemOrigem = $itensOrigemPorId->get(
                    $itemInformado['entrega_item_origem_id']
                );

                $itemPrincipalId = (int) (
                    $itemOrigem->entrega_item_principal_id
                    ?: $itemOrigem->id
                );

                EntregaItem::create([
                    'entrega_id' =>
                        $entregaFilha->id,

                    'entrega_item_origem_id' =>
                        $itemOrigem->id,

                    'entrega_item_principal_id' =>
                        $itemPrincipalId,

                    'item_orcamento_id' =>
                        $itemOrigem->item_orcamento_id,

                    'venda_item_id' =>
                        $itemOrigem->venda_item_id,

                    'quantidade_prevista' =>
                        $itemInformado['quantidade'],

                    'quantidade_entregue' =>
                        0,

                    'quantidade_recusada' =>
                        0,

                    'quantidade_devolvida' =>
                        0,

                    'quantidade_avariada' =>
                        0,

                    'motivo_nao_entrega' =>
                        null,

                    'status' =>
                        'Pendente',

                    'observacao' =>
                        $observacaoInformada !== ''
                            ? $observacaoInformada
                            : null,
                ]);
            }

            return $entregaFilha->fresh('itens');
        });
    }

    public function gerarEntregaDoOrcamento(Orcamento $orcamento): ?Entrega
    {
        return DB::transaction(function () use ($orcamento) {
            $orcamento->loadMissing([
                'cliente',
                'itens',
            ]);

            if (($orcamento->tipo_entrega ?? null) !== 'entrega') {
                return null;
            }

            $entregaExistente = Entrega::query()
                ->where('orcamento_id', $orcamento->id)
                ->lockForUpdate()
                ->first();

            if ($entregaExistente) {
                return $entregaExistente->load('itens');
            }

            $cliente = $orcamento->cliente;
            $endereco = $cliente?->endereco_entrega;

            if (empty($endereco)) {
                $endereco = trim(
                    implode(
                        ', ',
                        array_filter([
                            $cliente?->endereco,
                            $cliente?->numero,
                            $cliente?->bairro,
                            $cliente?->cidade,
                            $cliente?->estado,
                            $cliente?->cep,
                        ])
                    )
                );
            }

            $entrega = Entrega::create([
                'orcamento_id' => $orcamento->id,
                'venda_id' => null,
                'codigo_entrega' => $this->gerarCodigo(),
                'data_prevista' => $orcamento->data_prevista_entrega ?? now()->toDateString(),
                'data_prevista_entrega' => $orcamento->data_prevista_entrega ?? null,
                'periodo_entrega' => $orcamento->periodo_entrega ?? null,
                'observacao_entrega' => $orcamento->observacao_entrega ?? null,
                'data_realizada' => null,
                'status' => 'Pendente_pagamento',
                'cobrar_frete' => $orcamento->cobrar_frete ?? 0,
                'valor_frete' => $orcamento->valor_frete ?? 0,
                'tipo_entrega' => $orcamento->tipo_entrega ?? 'entrega',
                'usar_endereco_cliente' => 1,
                'endereco_entrega' => $endereco,
                'responsavel_recebimento' => $cliente?->nome,
                'telefone_recebimento' => $cliente?->telefone,
                'observacao' => 'Pré-entrega gerada automaticamente pelo orçamento #' . $orcamento->id,
            ]);

            foreach ($orcamento->itens as $item) {
                EntregaItem::create([
                    'entrega_id' => $entrega->id,
                    'item_orcamento_id' => $item->id,
                    'venda_item_id' => null,
                    'quantidade_prevista' => $item->quantidade_atendida > 0
                        ? $item->quantidade_atendida
                        : $item->quantidade_solicitada,
                    'quantidade_entregue' => 0,
                    'quantidade_recusada' => 0,
                    'quantidade_devolvida' => 0,
                    'quantidade_avariada' => 0,
                    'status' => 'Pendente',
                    'observacao' => null,
                ]);
            }

            return $entrega->fresh('itens');
        });
    }

    public function faturarEntregaDoOrcamento(Orcamento $orcamento, Venda $venda, $itensVendaCriados): ?Entrega
    {
        return DB::transaction(function () use ($orcamento, $venda, $itensVendaCriados) {
            $orcamento->loadMissing('itens');

            $entrega = Entrega::query()
                ->with('itens')
                ->where('orcamento_id', $orcamento->id)
                ->where('status', 'Pendente_pagamento')
                ->lockForUpdate()
                ->first();

            if (! $entrega) {
                return null;
            }

            $entrega->update([
                'venda_id' => $venda->id,
                'status' => 'Aguardando_separacao',
            ]);

            foreach ($entrega->itens as $entregaItem) {
                $itemOrcamento = $orcamento->itens
                    ->firstWhere('id', $entregaItem->item_orcamento_id);

                if (! $itemOrcamento) {
                    continue;
                }

                $itemVenda = collect($itensVendaCriados)
                    ->firstWhere('produto_id', $itemOrcamento->produto_id);

                if (! $itemVenda) {
                    continue;
                }

                $entregaItem->update([
                    'venda_item_id' => $itemVenda->id,
                    'status' => 'Pendente',
                ]);
            }
            $this->registrarBackupFaturamento(
                $entrega,
                $venda,
                $orcamento
            );

            return $entrega->fresh('itens');
        });
    }

    public function atribuirEquipe(Entrega $entrega, int $motoristaId, int $veiculoId): Entrega
    {
        $this->bloquearSeFinalizada($entrega);

        return DB::transaction(function () use ($entrega, $motoristaId, $veiculoId) {
            $motorista = Funcionario::query()
                ->where('id', $motoristaId)
                ->where('funcao', 'motorista')
                ->where('ativo', 1)
                ->firstOrFail();

            $veiculo = Veiculo::query()
                ->where('id', $veiculoId)
                ->where('ativo', 1)
                ->firstOrFail();

            $entrega->update([
                'motorista_id' => $motorista->id,
                'veiculo_id' => $veiculo->id,
                'status' => 'Aguardando_separacao',
            ]);

            return $entrega->fresh([
                'motorista',
                'veiculo',
            ]);
        });
    }

    private function registrarBackupFaturamento(Entrega $entrega, Venda $venda, Orcamento $orcamento): void
    {
        DB::table('entregas_backup')->insert([
            'venda_id' => $venda->id,
            'venda_item_id' => null,
            'data_prevista' => $entrega->data_prevista,
            'data_realizada' => null,
            'responsavel_entrega' => $entrega->responsavel_recebimento,
            'status' => 'pendente',
            'observacao' => 'Entrega liberada para separação após faturamento da venda #' .
                $venda->id .
                ' vinculada ao orçamento #' .
                $orcamento->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function alterarStatus(Entrega $entrega, string $novoStatus): Entrega
    {
        $this->validarStatus($novoStatus);
        $this->bloquearSeFinalizada($entrega);

        $entrega->update([
            'status' => $novoStatus,
            'data_realizada' => $novoStatus === 'Entregue'
                ? now()->toDateString()
                : $entrega->data_realizada,
        ]);

        return $entrega->fresh('itens');
    }

    public function confirmarEntrega(Entrega $entrega): Entrega
    {
        $this->bloquearSeFinalizada($entrega);

        return DB::transaction(function () use ($entrega) {
            $entrega->loadMissing('itens');

            foreach ($entrega->itens as $item) {
                $item->update([
                    'quantidade_entregue' => $item->quantidade_prevista,
                    'quantidade_recusada' => 0,
                    'quantidade_devolvida' => 0,
                    'quantidade_avariada' => 0,
                    'motivo_nao_entrega' => null,
                    'status' => 'Entregue',
                ]);
            }

            $entrega->update([
                'status' => 'Entregue',
                'data_realizada' => now()->toDateString(),
            ]);

            return $entrega->fresh('itens');
        });
    }

    public function confirmarParcial(Entrega $entrega, array $itens): Entrega
    {
        $this->bloquearSeFinalizada($entrega);

        return DB::transaction(function () use ($entrega, $itens) {
            foreach ($itens as $itemData) {
                $item = EntregaItem::query()
                    ->where('entrega_id', $entrega->id)
                    ->where('id', $itemData['entrega_item_id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                $quantidadeEntregue = round(
                    (float) $itemData['quantidade_entregue'],
                    2
                );

                if ($quantidadeEntregue < 0) {
                    throw ValidationException::withMessages([
                        'quantidade_entregue' => 'A quantidade entregue não pode ser negativa.',
                    ]);
                }

                if ($quantidadeEntregue > (float) $item->quantidade_prevista) {
                    throw ValidationException::withMessages([
                        'quantidade_entregue' => 'A quantidade entregue não pode ser maior que a quantidade prevista.',
                    ]);
                }

                $statusItem = match (true) {
                    $quantidadeEntregue >= (float) $item->quantidade_prevista =>
                        'Entregue',

                    $quantidadeEntregue > 0 =>
                        'Entregue_parcial',

                    default =>
                        'Pendente',
                };

                $item->update([
                    'quantidade_entregue' => $quantidadeEntregue,
                    'status' => $statusItem,
                ]);
            }

            $entrega->refresh();
            $entrega->load('itens');

            $todosEntregues = $entrega->itens->every(
                fn (EntregaItem $item) =>
                    (float) $item->quantidade_entregue
                    >= (float) $item->quantidade_prevista
            );

            $algumaQuantidadeEntregue = $entrega->itens->contains(
                fn (EntregaItem $item) =>
                    (float) $item->quantidade_entregue > 0
            );

            $novoStatus = match (true) {
                $todosEntregues =>
                    'Entregue',

                $algumaQuantidadeEntregue =>
                    'Entregue_parcial',

                default =>
                    'Nao_entregue',
            };

            $entrega->update([
                'status' => $novoStatus,
                'data_realizada' => $novoStatus === 'Entregue'
                    ? now()->toDateString()
                    : null,
            ]);

            return $entrega->fresh('itens');
        });
    }

    public function cancelar(Entrega $entrega, ?string $motivo = null): Entrega
    {
        if ($entrega->status === 'Entregue') {
            throw ValidationException::withMessages([
                'entrega' => 'Não é possível cancelar uma entrega já concluída.',
            ]);
        }

        if ($entrega->status === 'Cancelada') {
            throw ValidationException::withMessages([
                'entrega' => 'Esta entrega já está cancelada.',
            ]);
        }

        if (in_array($entrega->status, ['Em_rota', 'No_destino'], true)) {
            throw ValidationException::withMessages([
                'entrega' => 'A entrega em rota deve passar pela tratativa de retorno.',
            ]);
        }

        return DB::transaction(function () use ($entrega, $motivo) {
            $observacao = trim(
                ($entrega->observacao ?? '') .
                "\nCancelamento: " .
                ($motivo ?: 'Entrega cancelada pelo usuário.')
            );

            $entrega->update([
                'status' => 'Cancelada',
                'observacao' => $observacao,
            ]);

            $entrega->itens()->update([
                'status' => 'Cancelado',
            ]);

            return $entrega->fresh('itens');
        });
    }

    private function validarStatus(string $status): void
    {
        if (! in_array($status, $this->statusValidos, true)) {
            throw ValidationException::withMessages([
                'status' => 'Status de entrega inválido.',
            ]);
        }
    }

    private function bloquearSeFinalizada(Entrega $entrega): void
    {
        if (in_array($entrega->status, $this->statusFinais, true)) {
            throw ValidationException::withMessages([
                'entrega' => 'Esta entrega já está finalizada e não pode mais ser alterada.',
            ]);
        }
    }

    private function gerarCodigoRomaneio(): string
    {
        do {
            $codigo = 'ROM-' .
                now()->format('YmdHis') .
                '-' .
                random_int(100, 999);
        } while (
            Romaneio::query()
                ->where('codigo_romaneio', $codigo)
                ->exists()
        );

        return $codigo;
    }

    private function gerarCodigo(): string
    {
        do {
            $codigo = 'ENT-' .
                now()->format('YmdHis') .
                '-' .
                random_int(100, 999);
        } while (
            Entrega::query()
                ->where('codigo_entrega', $codigo)
                ->exists()
        );

        return $codigo;
    }
}