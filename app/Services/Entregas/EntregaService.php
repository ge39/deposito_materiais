<?php

namespace App\Services\Entregas;

use App\Models\Entrega;
use App\Models\EntregaItem;
use App\Models\ItemVenda;
use App\Models\Orcamento;
use App\Models\Venda;
use App\Models\Funcionario;
use App\Models\Veiculo;
use App\Models\Romaneio;
use App\Models\RomaneioItem;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EntregaService
{
    private array $statusValidos = [
        'Pendente_pagamento',
        'Aguardando_faturamento',
        'Aguardando_separacao',
        'Pendente',
        'Separando',
        'Carregado',
        'Em_rota',
        'Entregue',
        'Parcial',
        'Devolvido',
        'Cancelado',
    ];

    private array $statusFinais = [
        'Entregue',
        'Devolvido',
        'Cancelado',
    ];

    /**
     * Cria uma entrega manualmente.
     */
    public function criar(array $dados): Entrega
    {
        return DB::transaction(function () use ($dados) {
            $status = $dados['status'] ?? 'Pendente';

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

            if (!empty($dados['venda_id'])) {
                $entregaExistente = Entrega::where('venda_id', $dados['venda_id'])
                    ->lockForUpdate()
                    ->first();

                if ($entregaExistente) {
                    return $entregaExistente->load('itens');
                }
            }

            $entrega = Entrega::create([
                'orcamento_id'              => $dados['orcamento_id'] ?? null,
                'venda_id'                  => $dados['venda_id'] ?? null,
                'codigo_entrega'            => $dados['codigo_entrega'] ?? $this->gerarCodigo(),
                'data_prevista'             => $dados['data_prevista'],
                'data_realizada'            => null,
                'status'                    => $status,
                'tipo_entrega'              => $dados['tipo_entrega'] ?? 'entrega',
                'usar_endereco_cliente'     => $dados['usar_endereco_cliente'] ?? 1,
                'endereco_entrega'          => $dados['endereco_entrega'] ?? null,
                'responsavel_recebimento'   => $dados['responsavel_recebimento'] ?? null,
                'telefone_recebimento'      => $dados['telefone_recebimento'] ?? null,
                'observacao'                => $dados['observacao'] ?? null,
            ]);

            foreach ($dados['itens'] as $item) {
                EntregaItem::create([
                    'entrega_id'              => $entrega->id,
                    'venda_item_id'           => $item['venda_item_id'] ?? null,
                    'item_orcamento_id'       => $item['item_orcamento_id'] ?? null,
                    'quantidade_prevista'     => (float) $item['quantidade_prevista'],
                    'quantidade_entregue'     => 0,
                    'status'                  => 'Pendente',
                    'observacao'              => $item['observacao'] ?? null,
                ]);
            }

            return $entrega->fresh('itens');
        });
    }

    /**
     * Cria a pré-entrega quando o orçamento é aprovado.
     */
    public function gerarEntregaDoOrcamento(Orcamento $orcamento): ?Entrega
    {
        return DB::transaction(function () use ($orcamento) {
            $orcamento->loadMissing(['cliente', 'itens']);

            if (($orcamento->tipo_entrega ?? null) !== 'entrega') {
                return null;
            }

            $entregaExistente = Entrega::where('orcamento_id', $orcamento->id)
                ->lockForUpdate()
                ->first();

            if ($entregaExistente) {
                return $entregaExistente->load('itens');
            }

            $cliente = $orcamento->cliente;

            $endereco = $cliente->endereco_entrega ?? null;

            if (empty($endereco)) {
                $endereco = trim(implode(', ', array_filter([
                    $cliente->endereco ?? null,
                    $cliente->numero ?? null,
                    $cliente->bairro ?? null,
                    $cliente->cidade ?? null,
                    $cliente->estado ?? null,
                    $cliente->cep ?? null,
                ])));
            }

            $entrega = Entrega::create([
                'orcamento_id'              => $orcamento->id,
                'venda_id'                  => null,
                'codigo_entrega'            => $this->gerarCodigo(),
                'data_prevista'             => now()->toDateString(),
                'data_realizada'            => null,
                'status'                    => 'Pendente_pagamento',
                'tipo_entrega'              => $orcamento->tipo_entrega ?? 'entrega',
                'usar_endereco_cliente'     => 1,
                'endereco_entrega'          => $endereco,
                'responsavel_recebimento'   => $cliente->nome ?? null,
                'telefone_recebimento'      => $cliente->telefone ?? null,
                'observacao'                => 'Pré-entrega gerada automaticamente pelo orçamento #' . $orcamento->id,
            ]);

            
            foreach ($orcamento->itens as $item) {
                EntregaItem::create([
                    'entrega_id'              => $entrega->id,
                    'item_orcamento_id'       => $item->id,
                    'venda_item_id'           => null,
                    'quantidade_prevista'     => $item->quantidade_atendida > 0
                        ? $item->quantidade_atendida
                        : $item->quantidade_solicitada,
                    'quantidade_entregue'     => 0,
                    'status'                  => 'Pendente',
                    'observacao'              => null,
                ]);
            }

            return $entrega->fresh('itens');
        });
    }

    /**
     * Após a venda, libera a entrega para separação.
     */
    public function faturarEntregaDoOrcamento(
        Orcamento $orcamento,
        Venda $venda,
        $itensVendaCriados
        ): ?Entrega {
        return DB::transaction(function () use ($orcamento, $venda, $itensVendaCriados) {
            $orcamento->loadMissing('itens');

            $entrega = Entrega::with('itens')
                ->where('orcamento_id', $orcamento->id)
                ->where('status', 'Pendente_pagamento')
                ->lockForUpdate()
                ->first();

            if (!$entrega) {
                return null;
            }

            $entrega->update([
                'venda_id'   => $venda->id,
                'status'     => 'Aguardando_separacao',
                'updated_at' => now(),
            ]);

            foreach ($entrega->itens as $entregaItem) {
                $itemOrcamento = $orcamento->itens
                    ->where('id', $entregaItem->item_orcamento_id)
                    ->first();

                if (!$itemOrcamento) {
                    continue;
                }

                $itemVenda = collect($itensVendaCriados)
                    ->where('produto_id', $itemOrcamento->produto_id)
                    ->first();

                if (!$itemVenda) {
                    continue;
                }

                $entregaItem->update([
                    'venda_item_id' => $itemVenda->id,
                    'status'        => 'Pendente',
                    'updated_at'    => now(),
                ]);
            }

           $this->registrarBackupFaturamento($entrega, $venda, $orcamento);

            $this->gerarRomaneioDaEntrega($entrega);

            return $entrega->fresh('itens');
        });
    }

    public function atribuirEquipe(
            Entrega $entrega,
            int $motoristaId,
            int $veiculoId
        ): Entrega {

        $this->bloquearSeFinalizada($entrega);

        return DB::transaction(function () use ($entrega, $motoristaId, $veiculoId) {

            $motorista = Funcionario::where('id', $motoristaId)
                ->where('funcao', 'motorista')
                ->where('ativo', 1)
                ->firstOrFail();

            $veiculo = Veiculo::where('id', $veiculoId)
                ->where('ativo', 1)
                ->firstOrFail();

            $entrega->update([
                'motorista_id' => $motorista->id,
                'veiculo_id'   => $veiculo->id,
                'status'       => 'Aguardando_separacao',
            ]);

            return $entrega->fresh([
                'motorista',
                'veiculo',
            ]);
        });
    }

    private function gerarRomaneioDaEntrega(Entrega $entrega): Romaneio
    {
        $entrega->loadMissing('itens');

        $romaneioExistente = Romaneio::where('entrega_id', $entrega->id)
            ->first();

        if ($romaneioExistente) {
            return $romaneioExistente;
        }

        $romaneio = Romaneio::create([
            'entrega_id' => $entrega->id,
            'codigo_romaneio' => $this->gerarCodigoRomaneio(),
            'status' => 'Gerado',
            'data_emissao' => now(),
            'percentual_carregado' => 0,
        ]);

        foreach ($entrega->itens as $item) {
            RomaneioItem::create([
                'romaneio_id' => $romaneio->id,
                'entrega_item_id' => $item->id,
                'quantidade_prevista' => $item->quantidade_prevista,
                'quantidade_carregada' => 0,
                'status' => 'Pendente',
            ]);
        }

        return $romaneio->fresh('itens');
    }
    /**
     * Registra histórico da liberação da entrega.
     */
    private function registrarBackupFaturamento(
                Entrega $entrega,
                Venda $venda,
                Orcamento $orcamento
            ): void {
                DB::table('entregas_backup')->insert([
                    'venda_id'            => $venda->id,
                    'venda_item_id'       => null,
                    'data_prevista'       => $entrega->data_prevista,
                    'data_realizada'      => null,
                    'responsavel_entrega' => $entrega->responsavel_recebimento,
                    'status'              => 'pendente',
                    'observacao'          => 'Entrega liberada para separação após faturamento da venda #' . $venda->id . ' vinculada ao orçamento #' . $orcamento->id,
                    'created_at'          => now(),
                    'updated_at'          => now(),
                ]);
            }

            /**
             * Altera o status operacional da entrega.
             */
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

            /**
             * Confirma entrega total.
             */
            public function confirmarEntrega(Entrega $entrega): Entrega
            {
                $this->bloquearSeFinalizada($entrega);

                return DB::transaction(function () use ($entrega) {
                    $entrega->loadMissing('itens');

                    foreach ($entrega->itens as $item) {
                        $item->update([
                            'quantidade_entregue' => $item->quantidade_prevista,
                            'status'              => 'Entregue',
                        ]);
                    }

                    $entrega->update([
                        'status'          => 'Entregue',
                        'data_realizada'  => now()->toDateString(),
                    ]);

                    return $entrega->fresh('itens');
                });
            }

            /**
             * Confirma entrega parcial.
             */
            public function confirmarParcial(Entrega $entrega, array $itens): Entrega
            {
                $this->bloquearSeFinalizada($entrega);

                return DB::transaction(function () use ($entrega, $itens) {
                    foreach ($itens as $itemData) {
                        $item = EntregaItem::where('entrega_id', $entrega->id)
                            ->where('id', $itemData['entrega_item_id'])
                            ->firstOrFail();

                        $quantidadeEntregue = (float) $itemData['quantidade_entregue'];

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

                        $item->update([
                            'quantidade_entregue' => $quantidadeEntregue,
                            'status' => $quantidadeEntregue >= (float) $item->quantidade_prevista
                                ? 'Entregue'
                                : 'Pendente',
                        ]);
                    }

                    $totalItens = $entrega->itens()->count();

                    $itensEntregues = $entrega->itens()
                        ->where('status', 'Entregue')
                        ->count();

                    $novoStatus = $itensEntregues === $totalItens
                        ? 'Entregue'
                        : 'Parcial';

                    $entrega->update([
                        'status' => $novoStatus,
                        'data_realizada' => $novoStatus === 'Entregue'
                            ? now()->toDateString()
                            : null,
                    ]);

                    return $entrega->fresh('itens');
                });
            }

            /**
             * Cancela uma entrega.
             */
            public function cancelar(Entrega $entrega, ?string $motivo = null): Entrega
            {
                if ($entrega->status === 'Entregue') {
                    throw ValidationException::withMessages([
                        'entrega' => 'Não é possível cancelar uma entrega já concluída.',
                    ]);
                }

                if ($entrega->status === 'Cancelado') {
                    throw ValidationException::withMessages([
                        'entrega' => 'Esta entrega já está cancelada.',
                    ]);
                }

                return DB::transaction(function () use ($entrega, $motivo) {
                    $observacao = trim(
                        ($entrega->observacao ?? '') .
                        "\nCancelamento: " .
                        ($motivo ?: 'Entrega cancelada pelo usuário.')
                    );

                    $entrega->update([
                        'status'     => 'Cancelado',
                        'observacao' => $observacao,
                    ]);

                    $entrega->itens()->update([
                        'status' => 'Cancelado',
                    ]);

                    return $entrega->fresh('itens');
                });
            }

            /**
             * Valida status permitido.
             */
            private function validarStatus(string $status): void
            {
                if (!in_array($status, $this->statusValidos, true)) {
                    throw ValidationException::withMessages([
                        'status' => 'Status de entrega inválido.',
                    ]);
                }
            }

            /**
             * Impede alterar entrega finalizada.
             */
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
                    $codigo = 'ROM-' . now()->format('YmdHis') . '-' . random_int(100, 999);
                } while (Romaneio::where('codigo_romaneio', $codigo)->exists());

                return $codigo;
            }
            /**
             * Gera código único da entrega.
             */
            private function gerarCodigo(): string
            {
                do {
                    $codigo = 'ENT-' . now()->format('YmdHis') . '-' . random_int(100, 999);
                } while (Entrega::where('codigo_entrega', $codigo)->exists());

                return $codigo;
            }
    }