<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Empresa;
use App\Models\Caixa;
use App\Models\MovimentacaoCaixa;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Services\CaixaService;


class CaixaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    /**
     * Exibe a tela de abertura de caixa
     */
    public function abrir(Request $request)
    {
        $user = Auth::user();
        $terminal = $request->attributes->get('terminal');

        if (!$terminal) {
            Log::error('Terminal não identificado ao tentar abrir caixa.');
            return redirect()->back()->with('error', 'Terminal não identificado.');
        }

        $caixaAberto = Caixa::where('terminal_id', $terminal->id)
            ->where('status', 'aberto')
             ->latest('data_abertura')
            ->first();

        if ($caixaAberto) {
            Log::warning('Tentativa de abrir caixa já existente', [
                'terminal_id' => $terminal->id,
                'caixa_id' => $caixaAberto->id
            ]);
            return redirect()->route('pdv.index')
                ->with('warning', 'Caixa já está aberto para este terminal.');
        }

        return view('caixa.abrir', [
            'user' => $user,
            'terminal' => $terminal,
             'data_abertura' => $caixaAberto?->data_abertura,
        ]);
    }

    /**
     * Salva um novo caixa na tabela caixas e registra a abertura
     */
    // public function store(Request $request)
    // {

    //     $user = Auth::user();
    //     $terminal = $request->attributes->get('terminal');

    //     if (!$terminal) {
    //         return redirect()->back()->withErrors('Terminal não identificado.');
    //     }

              
    //     $caixaAberto = Caixa::where('terminal_id', $terminal->id)
    //         ->where('status', 'aberto')
    //         ->first();

    //     if ($caixaAberto) {
    //         return redirect()->route('pdv.index')
    //             ->with('warning', 'Caixa já aberto para este terminal.');
    //     }

    //     $valorFundoAnterior = $request->input('valor_fundo_anterior', 0.00);
    //     $fundoTroco         = $request->input('fundo_troco', 0.00);
    //     $valorAbertura      = $request->input('fundo_troco', 0.00);

    //     // Pega o primeiro ID da tabela empresa
    //     $empresaId = Empresa::query()->first()?->id;

    //     if (!$empresaId) {
    //         return redirect()->back()->withErrors('Não existe nenhuma empresa cadastrada.');
    //     }

    //     $caixa = Caixa::create([
    //         'user_id' => $user->id,
    //         'empresa_id' => $empresaId,
    //         'terminal_id' => $terminal->id,
    //         'terminal' => $terminal->identificador,
    //         'valor_fundo_anterior' => $valorFundoAnterior,
    //         'fundo_troco' => $fundoTroco,
    //         'divergencia_abertura' => $fundoTroco - $valorFundoAnterior,
    //         'valor' => $fundoTroco,
    //         'status' => 'aberto',
    //         'observacao' => $request->input('observacao'),
    //     ]);

    //         // Aqui chamamos o CaixaService para registrar a movimentação
    //         CaixaService::registrarMovimentacaoCaixa([
    //             'caixa_id'   => $caixa->id,
    //             'user_id'    => $user->id,
    //             'tipo'       => 'abertura',  
    //             'forma_pagamento' => 'abertura',         
    //             'valor'      => $valorAbertura,
    //             'origem_id'  => $caixa->id,  // VÍNCULO CORRETO
    //             'observacao' => 'Abertura de caixa',
    //         ]);


    //         return redirect()->route('pdv.index')
    //                         ->with('success', 'Caixa aberto com sucesso.')
    //                         ->with('caixa_id', $caixa->id);
    // }

//    public function store(Request $request) 
//     {
//         $user = Auth::user();
//         $terminal = $request->attributes->get('terminal');

//         if (!$terminal) {
//             return redirect()->back()->withErrors('Terminal não identificado.');
//         }

//         // 1. Evita abertura duplicada no mesmo terminal
//         $caixaAberto = Caixa::where('terminal_id', $terminal->id)
//             ->where('status', 'aberto')
//             ->first();

//         if ($caixaAberto) {
//             return redirect()->route('pdv.index')
//                 ->with('warning', 'Caixa já aberto para este terminal.');
//         }

//         // 2. Captura e valida o ID da empresa vindo do usuário logado
//         $empresaId = $user->empresa_id ?? null;

//         if (!$empresaId) {
//             return redirect()->back()->withErrors('O seu usuário não está vinculado a nenhuma empresa/filial.');
//         }

//         // Valida se a empresa está ativa no status 9
//         $empresaAtiva = Empresa::where('id', $empresaId)->where('ativo', 9)->exists();
//         if (!$empresaAtiva) {
//             return redirect()->back()->withErrors('Sua empresa/filial está inativa no sistema.');
//         }

//         $valorFundoAnterior = (float) $request->input('valor_fundo_anterior', 0.00);
//         $fundoTroco = (float) $request->input('fundo_troco', 0.00);

//         // 3. Criação do caixa usando os nomes exatos das colunas do seu banco
//         $caixa = Caixa::create([
//             'user_id'              => $user->id,
//             'empresa_id'           => $empresaId,
//             'terminal_id'          => $terminal->id,
//             'terminal'             => $terminal->identificador,
//             'valor_fundo_anterior' => $valorFundoAnterior,
//             'fundo_troco'          => $fundoTroco,
//             'divergencia_abertura' => $fundoTroco - $valorFundoAnterior,
//             'valor_abertura'       => $fundoTroco, // 👈 Ajustado (no código anterior estava 'valor')
//             'status'               => 'aberto',
//             'data_abertura'        => now(),
//             'observacao'           => $request->input('observacao'),
//         ]);

//         // 4. Registro da movimentação no service
//         CaixaService::registrarMovimentacaoCaixa([
//             'caixa_id'        => $caixa->id,
//             'user_id'         => $user->id,
//             'tipo'            => 'abertura',
//             'forma_pagamento' => 'abertura',
//             'valor'           => $fundoTroco,
//             'origem_id'       => $caixa->id,
//             'observacao'      => 'Abertura de caixa',
//         ]);

//         return redirect()->route('pdv.index')
//             ->with('success', 'Caixa aberto com sucesso.')
//             ->with('caixa_id', $caixa->id);
//     }

    // public function store(Request $request) 
    // {
    //     try {
    //         \Log::info('--- INICIANDO ABERTURA DE CAIXA ---');
            
    //         $user = Auth::user();
    //         \Log::info('Usuário Logado:', ['id' => $user?->id, 'empresa_id' => $user?->empresa_id]);

    //         $terminal = $request->attributes->get('terminal');
    //         \Log::info('Terminal Identificado:', ['terminal' => $terminal]);

    //         if (!$terminal) {
    //             \Log::warning('Falha: Terminal não identificado via request attributes.');
    //             return redirect()->back()->withErrors('Terminal não identificado.');
    //         }

    //         $caixaAberto = Caixa::where('terminal_id', $terminal->id)
    //             ->where('status', 'aberto')
    //             ->first();

    //         if ($caixaAberto) {
    //             \Log::warning('Falha: Já existe caixa aberto para este terminal.', ['caixa_id' => $caixaAberto->id]);
    //             return redirect()->route('pdv.index')->with('warning', 'Caixa já aberto para este terminal.');
    //         }

    //         // Diagnóstico do ID da empresa
    //         $empresaId = $user->empresa_id ?? null;
    //         \Log::info('Empresa ID avaliado:', ['empresa_id' => $empresaId]);

    //         if (!$empresaId) {
    //             \Log::warning('Falha: O usuário não possui empresa_id preenchido.');
    //             return redirect()->back()->withErrors('O seu usuário não está vinculado a nenhuma empresa/filial.');
    //         }

    //         $empresaAtiva = Empresa::where('id', $empresaId)->where('ativo', 9)->exists();
    //         \Log::info('Resultado da validação de empresa ativa (status 9):', ['ativa' => $empresaAtiva]);

    //         if (!$empresaAtiva) {
    //             \Log::warning('Falha: Empresa encontrada, mas o status não é 9 (Ativo).');
    //             return redirect()->back()->withErrors('Sua empresa/filial está inativa no sistema.');
    //         }

    //         $valorFundoAnterior = (float) $request->input('valor_fundo_anterior', 0.00);
    //         $fundoTroco = (float) $request->input('fundo_troco', 0.00);

    //         \Log::info('Dados financeiros capturados:', [
    //             'fundo_anterior' => $valorFundoAnterior,
    //             'fundo_troco' => $fundoTroco
    //         ]);

    //         \Log::info('Tentando persistir dados no banco...');
            
    //         $caixa = Caixa::create([
    //             'user_id'              => $user->id,
    //             'empresa_id'           => $empresaId,
    //             'terminal_id'          => $terminal->id,
    //             'terminal'             => $terminal->identificador,
    //             'valor_fundo_anterior' => $valorFundoAnterior,
    //             'fundo_troco'          => $fundoTroco,
    //             'divergencia_abertura' => $fundoTroco - $valorFundoAnterior,
    //             'valor_abertura'       => $fundoTroco,
    //             'status'               => 'aberto',
    //             'data_abertura'        => now(),
    //             'observacao'           => $request->input('observacao'),
    //         ]);

    //         \Log::info('Caixa salvo com sucesso no banco. ID gerado: ' . $caixa->id);

    //         \Log::info('Chamando CaixaService...');
    //         CaixaService::registrarMovimentacaoCaixa([
    //             'caixa_id'        => $caixa->id,
    //             'user_id'         => $user->id,
    //             'tipo'            => 'abertura',
    //             'forma_pagamento' => 'abertura',
    //             'valor'           => $fundoTroco,
    //             'origem_id'       => $caixa->id,
    //             'observacao'      => 'Abertura de caixa',
    //         ]);
            
    //         \Log::info('CaixaService finalizado com sucesso.');

    //         return redirect()->route('pdv.index')
    //             ->with('success', 'Caixa aberto com sucesso.')
    //             ->with('caixa_id', $caixa->id);

    //     } catch (\Exception $e) {
    //         \Log::error('EXCEÇÃO LANÇADA NA ABERTURA:', [
    //             'mensagem' => $e->getMessage(),
    //             'arquivo'  => $e->getFile(),
    //             'linha'    => $e->getLine()
    //         ]);
    //         return redirect()->back()->withErrors('Erro interno: ' . $e->getMessage());
    //     }
    // }

    public function store(Request $request) 
    {
        $user = Auth::user();
        $terminal = $request->attributes->get('terminal');

        if (!$terminal) {
            return redirect()->back()->withErrors('Terminal não identificado no sistema.');
        }

        // 1. Evita duplicidade no terminal atual
        $caixaAberto = Caixa::where('terminal_id', $terminal->id)
            ->where('status', 'aberto')
            ->first();

        if ($caixaAberto) {
            return redirect()->route('pdv.index')
                ->with('warning', 'Caixa já aberto para este terminal.');
        }

        // 2. Busca a empresa Gegames (ID 9, ativo 1)
        $empresaAtiva = Empresa::where('ativo', 1)->first();

        if (!$empresaAtiva) {
            return redirect()->back()->withErrors('Nenhuma empresa ativa encontrada no banco.');
        }

        $valorFundoAnterior = (float) $request->input('valor_fundo_anterior', 0.00);
        $fundoTroco = (float) $request->input('fundo_troco', 0.00);

        // Converte o identificador do terminal para string limpa para o banco
        $nomeTerminal = is_object($terminal) ? ($terminal->identificador ?? $terminal->nome) : (string) $terminal;

        try {
            // 3. Força a gravação limpando os tipos de dados
            $caixa = Caixa::create([
                'user_id'              => (int) $user->id,
                'empresa_id'           => (int) $empresaAtiva->id, // Grava o ID 9
                'terminal_id'          => (int) $terminal->id,
                'terminal'             => substr((string) $nomeTerminal, 0, 255), // Garante limite do VARCHAR
                'valor_fundo_anterior' => $valorFundoAnterior,
                'fundo_troco'          => $fundoTroco,
                'divergencia_abertura' => (float) ($fundoTroco - $valorFundoAnterior),
                'valor_abertura'       => $fundoTroco, 
                'status'               => 'aberto', // 👈 Força string exata do ENUM do banco
                'data_abertura'        => now()->format('Y-m-d H:i:s'), // Garante formato DATETIME
                'observacao'           => $request->input('observacao'),
            ]);

            // 4. Registro no service
            CaixaService::registrarMovimentacaoCaixa([
                'caixa_id'        => $caixa->id,
                'user_id'         => $user->id,
                'tipo'            => 'abertura',
                'forma_pagamento' => 'abertura',
                'valor'           => $fundoTroco,
                'origem_id'       => $caixa->id,
                'observacao'      => 'Abertura de caixa',
            ]);

            return redirect()->route('pdv.index')
                ->with('success', 'Caixa aberto com sucesso.')
                ->with('caixa_id', $caixa->id);

        } catch (\Exception $e) {
            // 🚨 Se o banco rejeitar por causa de alguma coluna, o erro aparecerá na tela no bloco que adicionamos no Blade
            return redirect()->back()->withErrors('Erro no Banco de Dados: ' . $e->getMessage());
        }
    }

    /**
     * Gera PDF do relatório do caixa
     */
    public function relatorioPdf($caixaId)
    {
        $caixa = Caixa::findOrFail($caixaId);

        $movimentacoes = MovimentacaoCaixa::where('caixa_id', $caixa->id)
            ->orderBy('data_movimentacao')
            ->get();

        $totais_por_tipo = $movimentacoes
            ->groupBy('tipo')
            ->map(fn ($items) => $items->sum('valor'))
            ->toArray();

        $pagamentos_por_forma = MovimentacaoCaixa::select(
            'forma_pagamento',
            \DB::raw('SUM(valor) as total')
        )
        ->where('caixa_id', $caixa->id)
        ->where('tipo', 'venda')
        ->groupBy('forma_pagamento')
        ->get();

        $saldo_sistema = $movimentacoes->sum(function ($mov) {
            return in_array($mov->tipo, ['venda', 'entrada_manual'])
                ? $mov->valor
                : -$mov->valor;
        });

        $pdf = Pdf::loadView('caixa.relatorio', compact(
            'caixa',
            'movimentacoes',
            'totais_por_tipo',
            'pagamentos_por_forma',
            'saldo_sistema'
        ))->setPaper('A4', 'portrait');

        return $pdf->stream("relatorio-caixa-{$caixa->id}.pdf");
    }
}