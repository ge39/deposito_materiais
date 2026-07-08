<?php

namespace App\Services\Expedicao;

use App\Models\Entrega;
use App\Models\Funcionario;
use App\Models\Romaneio;
use App\Models\RomaneioItem;
use App\Models\RomaneioEquipe;
use App\Models\Veiculo;
use App\Services\Logistica\EquipeService;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ExpedicaoService
{
    
    protected EquipeService $equipeService;

    public function __construct(EquipeService $equipeService)
    {
        $this->equipeService = $equipeService;
    }

    public function dashboard(Request $request): array
    {
        $status = $request->input('status');

        $dataInicio = $request->input(
            'data_inicio',
            now()->subDays(15)->toDateString()
        );

        $dataFim = $request->input(
            'data_fim',
            now()->toDateString()
        );

        $entregasDisponiveis = Entrega::with([
                'cliente',
                'itens.produto',
                'orcamento',
                'venda',
            ])
            ->whereIn('status', ['Aguardando_separacao', 'Separando'])
            ->orderBy('data_prevista_entrega')
            ->orderBy('id')
            ->get();

        $romaneios = Romaneio::with([
                'motorista',
                'veiculo',

                'entrega.cliente',
                'entrega.orcamento',
                'entrega.venda',

                'itens.entregaItem.entrega',
                'itens.entregaItem.produto',
                'itens.entregaItem.vendaItem.produto',
                'itens.entregaItem.itemOrcamento.produto',
            ])
            ->when($status, function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->whereBetween('data_emissao', [
                $dataInicio . ' 00:00:00',
                $dataFim . ' 23:59:59',
            ])
            ->orderByRaw("
                CASE status
                    WHEN 'Gerado' THEN 1
                    WHEN 'Em_separacao' THEN 2
                    WHEN 'Separado' THEN 3
                    WHEN 'Na_doca' THEN 4
                    WHEN 'Carregando' THEN 5
                    WHEN 'Carregado' THEN 6
                    WHEN 'Saiu_para_entrega' THEN 7
                    WHEN 'Entregue' THEN 8
                    WHEN 'Parcial' THEN 9
                    WHEN 'Devolvido' THEN 10
                    WHEN 'Cancelado' THEN 11
                    ELSE 99
                END
            ")
            ->orderBy('data_emissao')
            ->orderBy('id')
            ->get();

        $kpis = [
            'entregas_disponiveis' => $entregasDisponiveis->count(),

            'romaneios_abertos' => Romaneio::where('status', 'Gerado')->count(),
            'romaneios_em_separacao' => Romaneio::where('status', 'Em_separacao')->count(),
            'romaneios_separados' => Romaneio::where('status', 'Separado')->count(),
            'romaneios_na_doca' => Romaneio::where('status', 'Na_doca')->count(),

            'romaneios_carregando' => Romaneio::where('status', 'Carregando')->count(),
            'romaneios_carregados' => Romaneio::where('status', 'Carregado')->count(),
            'romaneios_em_rota' => Romaneio::where('status', 'Saiu_para_entrega')->count(),

            'romaneios_parciais' => Romaneio::where('status', 'Parcial')->count(),
            'romaneios_devolvidos' => Romaneio::where('status', 'Devolvido')->count(),
            'romaneios_cancelados' => Romaneio::where('status', 'Cancelado')->count(),
        ];

        return compact(
            'entregasDisponiveis',
            'romaneios',
            'kpis',
            'status',
            'dataInicio',
            'dataFim'
        );
    }

    public function carregarRomaneio(Romaneio $romaneio): array
    {
        $romaneio->load([
            'motorista',
            'veiculo',
            'entrega.cliente',
            'entrega.orcamento',
            'entrega.venda',

            'itens.entregaItem.entrega.orcamento.cliente',
            'itens.entregaItem.entrega.venda.cliente',

            'itens.entregaItem.produto',
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
            'entrega.cliente',
            'entrega.orcamento',
            'entrega.venda',

            'itens.entregaItem.entrega.orcamento.cliente',
            'itens.entregaItem.entrega.venda.cliente',

            'itens.entregaItem.produto',
            'itens.entregaItem.vendaItem.produto',
            'itens.entregaItem.itemOrcamento.produto',
        ]);

        $resumo = $this->montarResumoRomaneio($romaneio);

        return compact('romaneio', 'resumo');
    }

    public function iniciarSeparacao(Romaneio $romaneio): void
    {
        DB::transaction(function () use ($romaneio) {
            $romaneio = Romaneio::whereKey($romaneio->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($romaneio->status !== 'Gerado') {
                throw ValidationException::withMessages([
                    'romaneio' => 'Somente romaneios gerados podem iniciar separação.',
                ]);
            }

            $romaneio->update([
                'status' => 'Em_separacao',
                'data_inicio_separacao' => now(),
                'iniciado_por' => Auth::id(),
            ]);

            $romaneio->itens()
                ->where('status', 'Pendente')
                ->update([
                    'status' => 'Separando',
                ]);

            if ($romaneio->entrega) {
                $romaneio->entrega->update([
                    'status' => 'Separando',
                ]);
            }
        });
    }

    public function finalizarSeparacao(Romaneio $romaneio): void
    {
        DB::transaction(function () use ($romaneio) {
            $romaneio = Romaneio::with('itens.entregaItem.entrega')
                ->whereKey($romaneio->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($romaneio->status !== 'Em_separacao') {
                throw ValidationException::withMessages([
                    'romaneio' => 'Somente romaneios em separação podem ser finalizados.',
                ]);
            }

            if ($romaneio->itens->isEmpty()) {
                throw ValidationException::withMessages([
                    'romaneio' => 'Não é possível finalizar a separação de um romaneio sem itens.',
                ]);
            }

            foreach ($romaneio->itens as $item) {
                if (in_array($item->status, ['Cancelado', 'Devolvido'], true)) {
                    continue;
                }

                $item->update([
                    'status' => 'Separado',
                    'quantidade_separada' => $item->quantidade_prevista,
                    'separado_por' => Auth::id(),
                    'separado_em' => now(),
                ]);
            }

            $romaneio->update([
                'status' => 'Separado',
                'data_fim_separacao' => now(),
                'finalizado_por' => Auth::id(),
            ]);

            $entregas = $romaneio->itens
                ->pluck('entregaItem.entrega')
                ->filter()
                ->unique('id');

            foreach ($entregas as $entrega) {
                $entrega->update([
                    'status' => 'Separado',
                ]);
            }
        });
    }

    public function iniciarCarregamento(Romaneio $romaneio): void
    {
        DB::transaction(function () use ($romaneio) {
            $romaneio = Romaneio::whereKey($romaneio->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (!in_array($romaneio->status, ['Separado', 'Na_doca'], true)) {
                throw ValidationException::withMessages([
                    'romaneio' => 'Somente romaneios separados ou na doca podem iniciar carregamento.',
                ]);
            }

            $romaneio->update([
                'status' => 'Carregando',
                'data_inicio_carregamento' => now(),
                'carregado_por' => Auth::id(),
            ]);

            $romaneio->itens()
                ->whereIn('status', ['Separado', 'Conferido'])
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
            $romaneio = Romaneio::whereKey($romaneio->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($romaneio->status !== 'Carregando') {
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
                'carregado_por' => Auth::id(),
                'carregado_em' => now(),
                'conferido_por' => Auth::id(),
                'conferido_em' => now(),
                'observacao' => $observacao,
            ]);

            $this->atualizarPercentualCarregado($romaneio);
        });
    }

    public function finalizarCarregamento(Romaneio $romaneio): void
    {
        DB::transaction(function () use ($romaneio) {
            $romaneio = Romaneio::with('itens.entregaItem.entrega')
                ->whereKey($romaneio->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($romaneio->itens->isEmpty()) {
                throw ValidationException::withMessages([
                    'romaneio' => 'Não é possível finalizar um romaneio sem itens.',
                ]);
            }

            if ($romaneio->status !== 'Carregando') {
                throw ValidationException::withMessages([
                    'romaneio' => 'Somente romaneios em carregamento podem ser finalizados.',
                ]);
            }

            $pendentes = $romaneio->itens
                ->whereIn('status', [
                    'Pendente',
                    'Separando',
                    'Separado',
                    'Conferindo',
                    'Conferido',
                    'Carregando',
                ])
                ->count();

            if ($pendentes > 0) {
                throw ValidationException::withMessages([
                    'romaneio' => 'Existem itens pendentes de carregamento/conferência.',
                ]);
            }

            $temParcial = $romaneio->itens->where('status', 'Parcial')->count() > 0;

            $romaneio->update([
                'status' => $temParcial ? 'Parcial' : 'Carregado',
                'data_fim_carregamento' => now(),
                'percentual_carregado' => $this->calcularPercentualCarregado($romaneio),
                'carregado_por' => Auth::id(),
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
            $romaneio = Romaneio::with('itens.entregaItem.entrega')
                ->whereKey($romaneio->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($romaneio->status !== 'Carregado') {
                throw ValidationException::withMessages([
                    'romaneio' => 'Somente romaneios carregados podem ser liberados para rota.',
                ]);
            }

            $romaneio->update([
                'status' => 'Saiu_para_entrega',
                'data_saida' => now(),
            ]);

            $entregas = $romaneio->itens
                ->pluck('entregaItem.entrega')
                ->filter()
                ->unique('id');

            foreach ($entregas as $entrega) {
                $entrega->update([
                    'status' => 'Saiu_para_entrega',
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
            'separando' => $romaneio->itens->where('status', 'Separando')->count(),
            'separados' => $romaneio->itens->where('status', 'Separado')->count(),

            'conferindo' => $romaneio->itens->where('status', 'Conferindo')->count(),
            'conferidos' => $romaneio->itens->where('status', 'Conferido')->count(),

            'carregando' => $romaneio->itens->where('status', 'Carregando')->count(),
            'carregados' => $romaneio->itens->where('status', 'Carregado')->count(),
            'parciais' => $romaneio->itens->where('status', 'Parcial')->count(),
            'devolvidos' => $romaneio->itens->where('status', 'Devolvido')->count(),
            'cancelados' => $romaneio->itens->where('status', 'Cancelado')->count(),

            'progresso' => $totalItens > 0
                ? round(($romaneio->itens->whereIn('status', ['Carregado', 'Parcial'])->count() / $totalItens) * 100)
                : 0,
        ];
    }

    private function atualizarPercentualCarregado(Romaneio $romaneio): void
    {
        $romaneio->load('itens');

        $romaneio->update([
            'percentual_carregado' => $this->calcularPercentualCarregado($romaneio),
        ]);
    }

    private function calcularPercentualCarregado(Romaneio $romaneio): float
    {
        $totalItens = $romaneio->itens->count();

        if ($totalItens <= 0) {
            return 0;
        }

        $itensCarregados = $romaneio->itens
            ->whereIn('status', ['Carregado', 'Parcial'])
            ->count();

        return round(($itensCarregados / $totalItens) * 100, 2);
    }

     public function carregarTelaEquipe(Romaneio $romaneio): array
    {
        $romaneio->load([
            'entrega.orcamento.cliente',
            'motorista',
            'veiculo',
        ]);

        $motoristas = $this->equipeService->listarMotoristasDisponiveis();

        $veiculos = $this->equipeService->listarVeiculosParaRomaneio($romaneio);

        return compact(
            'romaneio',
            'motoristas',
            'veiculos'
        );
    }

   public function salvarEquipe(Romaneio $romaneio, array $dados): void
    {
        DB::transaction(function () use ($romaneio, $dados) {
            $romaneio = Romaneio::whereKey($romaneio->id)
                ->lockForUpdate()
                ->firstOrFail();

            $veiculo = Veiculo::whereKey($dados['veiculo_id'])
                ->lockForUpdate()
                ->firstOrFail();

            $motoristaId = (int) $dados['motorista_id'];
            $veiculoId = (int) $dados['veiculo_id'];

            $motoristaEmOutroRomaneio = RomaneioEquipe::where('motorista_id', $motoristaId)
                ->where('status', 'Ativa')
                ->where('romaneio_id', '!=', $romaneio->id)
                ->exists();

            // if ($motoristaEmOutroRomaneio) {
            //     throw ValidationException::withMessages([
            //         'motorista_id' => 'Este motorista já está atribuído a outro romaneio ativo.',
            //     ]);
            // }

            $veiculoEmOutroRomaneio = RomaneioEquipe::where('veiculo_id', $veiculoId)
                ->where('status', 'Ativa')
                ->where('romaneio_id', '!=', $romaneio->id)
                ->exists();

            if ($veiculoEmOutroRomaneio) {
                throw ValidationException::withMessages([
                    'veiculo_id' => "O veículo {$veiculo->placa} já está atribuído a outro romaneio ativo.",
                ]);
            }

            if (
                $veiculo->disponibilidade !== 'Disponivel' &&
                (int) $romaneio->veiculo_id !== $veiculoId
            ) {
                throw ValidationException::withMessages([
                    'veiculo_id' => "O veículo {$veiculo->placa} não está disponível.",
                ]);
            }

            $equipeAtivaAnterior = RomaneioEquipe::where('romaneio_id', $romaneio->id)
                ->where('status', 'Ativa')
                ->lockForUpdate()
                ->first();

            if ($equipeAtivaAnterior) {
                $equipeAtivaAnterior->update([
                    'status' => 'Substituida',
                    'liberado_por' => Auth::id(),
                    'liberado_em' => now(),
                    'motivo_substituicao' => 'Equipe substituída pela expedição.',
                ]);

                if ((int) $equipeAtivaAnterior->veiculo_id !== $veiculoId) {
                    Veiculo::whereKey($equipeAtivaAnterior->veiculo_id)
                        ->update([
                            'motorista_atual_id' => null,
                            'disponibilidade' => 'Disponivel',
                        ]);
                }
            }

            RomaneioEquipe::create([
                'romaneio_id' => $romaneio->id,
                'motorista_id' => $motoristaId,
                'veiculo_id' => $veiculoId,
                'status' => 'Ativa',
                'atribuido_por' => Auth::id(),
                'atribuido_em' => now(),
            ]);

            $romaneio->update([
                'motorista_id' => $motoristaId,
                'veiculo_id' => $veiculoId,
            ]);

            $veiculo->update([
                'motorista_atual_id' => $motoristaId,
                'disponibilidade' => 'Reservado',
            ]);
        });
    }
  
}
