<?php

namespace App\Services\Expedicao;

use App\Models\Entrega;
use App\Models\Romaneio;
use App\Models\RomaneioItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ExpedicaoService
{
    public function dashboard(Request $request): array
    {
        $status = $request->input('status');
        $data = $request->input('data', now()->toDateString());

        $entregasDisponiveis = Entrega::with(['cliente', 'itens.produto'])
            ->whereIn('status', ['Faturado', 'faturado'])
            ->orderBy('data_prevista')
            ->get();

        $romaneios = Romaneio::with(['motorista', 'veiculo'])
            ->when($status, function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->whereDate('data_emissao', $data)
            ->latest()
            ->get();

        $kpis = [
            'entregas_disponiveis' => $entregasDisponiveis->count(),
            'romaneios_abertos' => Romaneio::where('status', 'Aberto')->count(),
            'romaneios_carregando' => Romaneio::where('status', 'Carregando')->count(),
            'romaneios_carregados' => Romaneio::where('status', 'Carregado')->count(),
            'romaneios_em_rota' => Romaneio::where('status', 'Em Rota')->count(),
        ];

        return compact('entregasDisponiveis', 'romaneios', 'kpis', 'status', 'data');
    }

   public function carregarRomaneio(Romaneio $romaneio): array
    {
        $romaneio->load([
            'motorista',
            'veiculo',

            'itens.entregaItem.entrega.orcamento.cliente',
            'itens.entregaItem.entrega.venda.cliente',

            'itens.entregaItem.vendaItem.produto',
            'itens.entregaItem.itemOrcamento.produto',
        ]);

        $resumo = $this->montarResumoRomaneio($romaneio);

        return compact('romaneio', 'resumo');
    }

   public function carregarOperacao(Romaneio $romaneio): array
    {
        $romaneio->load([
            'motorista',
            'veiculo',

            'itens.entregaItem.entrega.orcamento.cliente',
            'itens.entregaItem.entrega.venda.cliente',

            'itens.entregaItem.vendaItem.produto',
            'itens.entregaItem.itemOrcamento.produto',
        ]);

        $resumo = $this->montarResumoRomaneio($romaneio);

        return compact('romaneio', 'resumo');
    }

    public function iniciarSeparacao(Romaneio $romaneio): void
    {
        DB::transaction(function () use ($romaneio) {
            if ($romaneio->status !== 'Aberto') {
                throw ValidationException::withMessages([
                    'romaneio' => 'Somente romaneios em aberto podem iniciar separação.',
                ]);
            }

            $romaneio->update([
                'status' => 'Em Separação',
                'iniciado_em' => now(),
                'iniciado_por' => Auth::id(),
            ]);

            $romaneio->itens()
                ->where('status', 'Pendente')
                ->update([
                    'status' => 'Separado',
                ]);
        });
    }

    public function iniciarCarregamento(Romaneio $romaneio): void
    {
        DB::transaction(function () use ($romaneio) {
            if (! in_array($romaneio->status, ['Aberto', 'Em Separação'], true)) {
                throw ValidationException::withMessages([
                    'romaneio' => 'Somente romaneios em aberto ou em separação podem iniciar carregamento.',
                ]);
            }

            $romaneio->update([
                'status' => 'Carregando',
                'iniciado_em' => $romaneio->iniciado_em ?? now(),
                'iniciado_por' => $romaneio->iniciado_por ?? Auth::id(),
            ]);

            $romaneio->itens()
                ->whereIn('status', ['Pendente', 'Separado'])
                ->update([
                    'status' => 'Carregando',
                ]);
        });
    }

    public function confirmarItemCarregado(
        Romaneio $romaneio,
        int $romaneioItemId,
        float $quantidadeCarregada,
        ?string $observacao = null
    ): void {
        DB::transaction(function () use ($romaneio, $romaneioItemId, $quantidadeCarregada, $observacao) {
            if (! in_array($romaneio->status, ['Carregando'], true)) {
                throw ValidationException::withMessages([
                    'romaneio' => 'Somente romaneios em carregamento permitem conferência de itens.',
                ]);
            }

            $item = RomaneioItem::where('romaneio_id', $romaneio->id)
                ->where('id', $romaneioItemId)
                ->lockForUpdate()
                ->firstOrFail();

            if ($quantidadeCarregada > (float) $item->quantidade_prevista) {
                throw ValidationException::withMessages([
                    'quantidade_carregada' => 'A quantidade carregada não pode ser maior que a quantidade prevista.',
                ]);
            }

            $item->update([
                'quantidade_carregada' => $quantidadeCarregada,
                'status' => $this->definirStatusItem(
                    (float) $item->quantidade_prevista,
                    $quantidadeCarregada
                ),
                'conferido_por' => Auth::id(),
                'conferido_em' => now(),
                'observacao' => $observacao,
            ]);
        });
    }

    public function finalizarCarregamento(Romaneio $romaneio): void
    {
        DB::transaction(function () use ($romaneio) {
            $romaneio->load('itens.entregaItem.entrega');

            if ($romaneio->itens->isEmpty()) {
                throw ValidationException::withMessages([
                    'romaneio' => 'Não é possível finalizar um romaneio sem itens.',
                ]);
            }

            $pendentes = $romaneio->itens
                ->whereIn('status', ['Pendente', 'Separado', 'Carregando'])
                ->count();

            if ($pendentes > 0) {
                throw ValidationException::withMessages([
                    'romaneio' => 'Existem itens pendentes de conferência.',
                ]);
            }

            $temParcial = $romaneio->itens->where('status', 'Parcial')->count() > 0;

            $romaneio->update([
                'status' => $temParcial ? 'Parcial' : 'Carregado',
                'finalizado_em' => now(),
                'finalizado_por' => Auth::id(),
            ]);

            $entregas = $romaneio->itens
                ->pluck('entregaItem.entrega')
                ->filter()
                ->unique('id');

            foreach ($entregas as $entrega) {
                $entrega->update([
                    'status' => $temParcial ? 'Parcial' : 'Carregado',
                ]);
            }
        });
    }

    public function liberarRota(Romaneio $romaneio): void
    {
        DB::transaction(function () use ($romaneio) {
            if ($romaneio->status !== 'Carregado') {
                throw ValidationException::withMessages([
                    'romaneio' => 'Somente romaneios carregados podem ser liberados para rota.',
                ]);
            }

            $romaneio->load('itens.entregaItem.entrega');

            $romaneio->update([
                'status' => 'Em Rota',
                'liberado_em' => now(),
                'liberado_por' => Auth::id(),
            ]);

            $entregas = $romaneio->itens
                ->pluck('entregaItem.entrega')
                ->filter()
                ->unique('id');

            foreach ($entregas as $entrega) {
                $entrega->update([
                    'status' => 'Em Rota',
                ]);
            }
        });
    }

    private function definirStatusItem(float $prevista, float $carregada): string
    {
        if ($carregada <= 0) {
            return 'Carregando';
        }

        if ($carregada < $prevista) {
            return 'Parcial';
        }

        return 'Carregado';
    }

    private function montarResumoRomaneio(Romaneio $romaneio): array
    {
        $totalItens = $romaneio->itens->count();

        return [
            'total_itens' => $totalItens,
            'pendentes' => $romaneio->itens->where('status', 'Pendente')->count(),
            'separados' => $romaneio->itens->where('status', 'Separado')->count(),
            'carregando' => $romaneio->itens->where('status', 'Carregando')->count(),
            'carregados' => $romaneio->itens->where('status', 'Carregado')->count(),
            'parciais' => $romaneio->itens->where('status', 'Parcial')->count(),
            'progresso' => $totalItens > 0
                ? round(($romaneio->itens->whereIn('status', ['Carregado', 'Parcial'])->count() / $totalItens) * 100)
                : 0,
        ];
    }
}