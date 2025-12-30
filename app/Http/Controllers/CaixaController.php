<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Caixa;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CaixaController extends Controller
{
    public function __construct()
    {
        // Aplica middleware auth em todas as rotas do Caixa
        $this->middleware('auth');
    }
    
    /**
     * Exibe a tela de abertura de caixa
     */
    public function abrir(Request $request)
    {
        $user = Auth::user();
        $terminal = $request->attributes->get('terminal'); // passado pelo middleware IdentificaTerminal

        if (!$terminal) {
            Log::error('Terminal não identificado ao tentar abrir caixa.');
            return redirect()->back()->with('error', 'Terminal não identificado.');
        }

        // Verifica se já existe caixa aberto neste terminal
        $caixaAberto = Caixa::where('terminal_id', $terminal->id)
            ->where('status', 'aberto')
            ->first();

        if ($caixaAberto) {
            Log::warning('Tentativa de abrir caixa já existente', [
                'terminal_id' => $terminal->id,
                'caixa_id' => $caixaAberto->id
            ]);
            return redirect()->route('pdv.index')
                ->with('warning', 'Caixa já está aberto para este terminal.');
        }

        // Aqui você apenas exibe a view para o usuário preencher valores e confirmar
        return view('caixa.abrir', [
            'user' => $user,
            'terminal' => $terminal,
        ]);
    }
    /**
     * Salva um novo caixa na tabela caixas
     */
    // public function store(Request $request)
    // {
    //     $user = Auth::user();
    //     $terminal = $request->attributes->get('terminal'); // middleware IdentificaTerminal

    //     if (!$terminal) {
    //         Log::error('Terminal não identificado ao tentar abrir caixa.');
    //         return response()->json(['error' => 'Terminal não identificado.'], 400);
    //     }

    //     // Log do request completo para debug
    //     Log::info('Tentativa de abrir caixa', [
    //         'request_data' => $request->all(),
    //         'user_id' => $user->id,
    //         'terminal_id' => $terminal->id,
    //     ]);

    //     // Checa se já existe caixa aberto neste terminal
    //     $caixaAberto = Caixa::where('terminal_id', $terminal->id)
    //         ->where('status', 'aberto')
    //         ->first();

    //     if ($caixaAberto) {
    //         Log::warning('Caixa já aberto', [
    //             'terminal_id' => $terminal->id,
    //             'caixa_id' => $caixaAberto->id
    //         ]);
    //         // return response()->json(['message' => 'Caixa já aberto para este terminal.'], 200);
    //          return view('pdv.index');
    //     }

    //     // Cria o caixa
    //     try {
    //         $caixa = Caixa::create([
    //             'user_id' => $user->id,
    //             'terminal_id' => $terminal->id,
    //             'terminal' => $terminal->identificador,
    //             'valor_fundo_anterior' => $request->input('valor_fundo_anterior', 0.00),
    //             'fundo_troco' => $request->input('fundo_troco', 0.00),
    //             'divergencia_abertura' => $request->input('divergencia_abertura', 0.00),
    //             'valor_abertura' => $request->input('valor_abertura', 0.00),
    //             'status' => 'aberto',
    //             'observacao' => $request->input('observacao', null),
    //         ]);

    //         // Log::info('Caixa salvo com sucesso', ['caixa_id' => $caixa->id]);

    //         // return response()->json(['success' => true, 'caixa_id' => $caixa->id], 201);
    //          // Redireciona para o PDV com caixa aberto
           
                            
    //     } catch (\Exception $e) {
    //         Log::error('Erro ao salvar caixa', [
    //             'message' => $e->getMessage(),
    //             'trace' => $e->getTraceAsString(),
    //             'request_data' => $request->all(),
    //         ]);

    //         return response()->json(['error' => 'Não foi possível salvar o caixa'], 500);
    //     }
    // }
    public function store(Request $request)
    {
        $user = Auth::user();
        $terminal = $request->attributes->get('terminal');

        if (!$terminal) {
            return redirect()->back()->withErrors('Terminal não identificado.');
        }

        // Checa se já existe caixa aberto neste terminal
        $caixaAberto = Caixa::where('terminal_id', $terminal->id)
            ->where('status', 'aberto')
            ->first();

        if ($caixaAberto) {
            return redirect()->route('pdv.index')
                            ->with('warning', 'Caixa já aberto para este terminal.');
        }

        // Cria o caixa
        $caixa = Caixa::create([
            'user_id' => $user->id,
            'terminal_id' => $terminal->id,
            'terminal' => $terminal->identificador,
            'valor_fundo_anterior' => $request->input('valor_fundo_anterior', 0.00),
            'fundo_troco' => $request->input('fundo_troco', 0.00),
            'divergencia_abertura' => $request->input('fundo_troco', 0.00)-$request->input('valor_fundo_anterior', 0.00),
            'valor_abertura' => $request->input('fundo_troco', 0.00),
            'status' => 'aberto',
            'observacao' => $request->input('observacao', null),
        ]);

        // Redireciona para o PDV com caixa aberto
        return redirect()->route('pdv.index')
                        ->with('success', 'Caixa aberto com sucesso.')
                        ->with('caixa_id', $caixa->id);
    }

}
