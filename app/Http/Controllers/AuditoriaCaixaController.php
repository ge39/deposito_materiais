<?php

namespace App\Http\Controllers;

use App\Models\Caixa;
use App\Models\AuditoriaCaixa;
use App\Models\MovimentacaoCaixa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AuditoriaCaixaController extends Controller
{
    /**
     * Iniciar auditoria de um caixa
     */
    public function iniciar(Request $request, Caixa $caixa)
    {
        $request->validate([
            'dinheiro'        => 'required|numeric|min:0',
            'pix'             => 'required|numeric|min:0',
            'carteira'        => 'required|numeric|min:0',
            'cartao_debito'   => 'required|numeric|min:0',
            'cartao_credito'  => 'required|numeric|min:0',
        ]);

        $userId = Auth::id();

        return DB::transaction(function () use ($request, $caixa, $userId) {

            $valoresFisicos = $request->only([
                'dinheiro','pix','carteira','cartao_debito','cartao_credito'
            ]);

            /*
            |--------------------------------------------------------------------------
            | 1️⃣ Calcular total do sistema por forma
            |--------------------------------------------------------------------------
            */
            $pagamentos = $caixa->vendas
                ->flatMap->pagamentos
                ->where('status', 'confirmado');

            $totalSistema = $pagamentos->sum('valor');
            $totalFisico  = array_sum($valoresFisicos);
            $diferenca    = $totalFisico - $totalSistema;

            /*
            |--------------------------------------------------------------------------
            | 2️⃣ Criar auditoria
            |--------------------------------------------------------------------------
            */
            $auditoria = AuditoriaCaixa::create([
                'caixa_id'        => $caixa->id,
                'user_id'         => $userId,
                'codigo_auditoria'=> $this->gerarCodigoAuditoria($caixa->id),
                'total_sistema'   => $totalSistema,
                'total_fisico'    => $totalFisico,
                'diferenca'       => $diferenca,
                'status'          => $diferenca == 0 ? 'concluida' : 'inconsistente',
                'data_auditoria'  => now(),
            ]);

            /*
            |--------------------------------------------------------------------------
            | 3️⃣ Vincular movimentações ao ID da auditoria
            |--------------------------------------------------------------------------
            */
            MovimentacaoCaixa::where('caixa_id', $caixa->id)
                ->whereNull('auditoria_id')
                ->update([
                    'auditoria_id' => $auditoria->id
                ]);

            /*
            |--------------------------------------------------------------------------
            | 4️⃣ Atualizar status do caixa
            |--------------------------------------------------------------------------
            */
            $caixa->update([
                'status' => $diferenca == 0 ? 'fechado' : 'inconsistente'
            ]);

            return redirect()
                ->route('fechamento.confirmacao', $caixa->id)
                ->with('success', 'Auditoria realizada com sucesso.');
        });
    }

    /**
     * Gerar código único de auditoria
     */
    private function gerarCodigoAuditoria($caixaId)
    {
        return 'AUD-' . $caixaId . '-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(4));
    }
}
