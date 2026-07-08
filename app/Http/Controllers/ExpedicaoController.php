<?php

namespace App\Http\Controllers;

use App\Models\Romaneio;
use App\Services\Expedicao\ExpedicaoService;
use Illuminate\Http\Request;

class ExpedicaoController extends Controller
{
    protected ExpedicaoService $expedicaoService;

    public function __construct(ExpedicaoService $expedicaoService)
    {
        $this->expedicaoService = $expedicaoService;
    }

    /**
     * Dashboard operacional da Expedição.
     */
    public function index(Request $request)
    {
        $dados = $this->expedicaoService->dashboard($request);

        return view('expedicao.index', $dados);
    }

    /**
     * Painel operacional de um romaneio.
     */
    public function show(Romaneio $romaneio)
    {
        $dados = $this->expedicaoService->carregarRomaneio($romaneio);

        return view('expedicao.show', $dados);
    }

    /**
     * Tela de atribuição de motorista e veículo ao romaneio.
     */
    public function atribuirEquipe(Romaneio $romaneio)
    {
        $dados = $this->expedicaoService->carregarTelaEquipe($romaneio);

        return view('expedicao.atribuir-equipe', $dados);
    }

    /**
     * Salva motorista e veículo no romaneio.
     */
    public function salvarEquipe(Request $request, Romaneio $romaneio)
    {
        $request->validate([
            'motorista_id' => ['required', 'integer', 'exists:funcionarios,id'],
            'veiculo_id' => ['required', 'integer', 'exists:veiculos,id'],
        ]);

        try {
            $this->expedicaoService->salvarEquipe($romaneio, [
                'motorista_id' => $request->motorista_id,
                'veiculo_id' => $request->veiculo_id,
            ]);

            return redirect()
                ->route('expedicao.index')
                ->with('success', 'Equipe atribuída ao romaneio com sucesso.');
        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->with('error', 'Erro ao atribuir equipe: ' . $e->getMessage());
        }
    }

    /**
     * Tela de operação (separação/carregamento/conferência).
     */
    public function operacao(Romaneio $romaneio)
    {
        $dados = $this->expedicaoService->carregarOperacao($romaneio);

        return view('expedicao.operacao', $dados);
    }

    /**
     * Inicia o processo de separação.
     */
    public function iniciarSeparacao(Romaneio $romaneio)
    {
        $this->expedicaoService->iniciarSeparacao($romaneio);

        return back()->with('success', 'Separação iniciada com sucesso.');
    }

    /**
     * Inicia o carregamento.
     */
    public function iniciarCarregamento(Romaneio $romaneio)
    {
        $this->expedicaoService->iniciarCarregamento($romaneio);

        return back()->with('success', 'Carregamento iniciado com sucesso.');
    }

    public function confirmarItem(Request $request, Romaneio $romaneio)
    {
        $request->validate([
            'romaneio_item_id' => ['required', 'integer', 'exists:romaneio_itens,id'],
            'quantidade_carregada' => ['required', 'numeric', 'min:0'],
            'observacao' => ['nullable', 'string'],
        ]);

        try {
            $this->expedicaoService->confirmarItemCarregado(
                $romaneio,
                (int) $request->romaneio_item_id,
                (float) $request->quantidade_carregada,
                $request->observacao
            );

            return back()->with('success', 'Item conferido com sucesso.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Erro ao conferir item: ' . $e->getMessage());
        }
    }

    /**
     * Finaliza o carregamento.
     */
    public function finalizarCarregamento(Romaneio $romaneio)
    {
        $this->expedicaoService->finalizarCarregamento($romaneio);

        return back()->with('success', 'Carregamento finalizado com sucesso.');
    }

    /**
     * Libera o veículo para rota.
     */
    public function liberarRota(Romaneio $romaneio)
    {
        $this->expedicaoService->liberarRota($romaneio);

        return back()->with('success', 'Romaneio liberado para rota.');
    }
}