<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LimiteClienteController extends Controller
{
    /**
     * 📊 Método principal (novo padrão)
     */
    public function index(Request $request)
    {
        return $this->tela($request);
    }

    /**
     * 📊 Método compatível com sua rota atual (NÃO REMOVA)
     */
    public function tela(Request $request = null)
    {
        $query = DB::table('vw_cliente_credito_resumo');

        // 🔍 filtro por nome
        if ($request && $request->filled('busca')) {
            $query->where('nome', 'like', '%' . $request->busca . '%');
        }

        // 🔽 filtro por status
        if ($request && $request->filled('status')) {
            $query->where('status', $request->status);
        }

        $clientes = $query
            ->orderBy('nome', 'asc')
            ->get();

        return view('limites.index', compact('clientes'));
    }

    /**
     * 🔴 Estourados
     */
    public function estourados()
    {
        $clientes = DB::table('vw_cliente_credito_resumo')
            ->where('credito_disponivel', '<', 0)
            ->get();

        return response()->json($clientes);
    }

    /**
     * 🟡 Risco
     */
    public function risco()
    {
        $clientes = DB::table('vw_cliente_credito_resumo')
            ->whereRaw('(total_usado / limite_credito) >= 0.8')
            ->get();

        return response()->json($clientes);
    }

    /**
     * 🔍 Cliente específico
     */
    public function show($id)
    {
        $cliente = DB::table('vw_cliente_credito_resumo')
            ->where('cliente_id', $id)
            ->first();

        if (!$cliente) {
            return response()->json(['erro' => 'Cliente não encontrado'], 404);
        }

        return response()->json($cliente);
    }

    /**
     * 🚫 Bloquear crédito
     */
    public function bloquear($id)
    {
        $cliente = DB::table('vw_cliente_credito_resumo')
            ->where('cliente_id', $id)
            ->first();

        if (!$cliente) {
            return redirect('/limites-view')
                ->with('error', 'Cliente não encontrado');
        }

        DB::table('cliente_creditos')
            ->where('cliente_id', $id)
            ->update([
                'status' => 'bloqueado',
                'updated_at' => now()
            ]);

        return redirect('/limites-view')
            ->with('success', "Cliente {$cliente->nome} ,bloqueado com sucesso!!");
    }

    /**
     * 🔓 Desbloquear crédito
     */
    public function desbloquear($id)
    {
        $cliente = DB::table('vw_cliente_credito_resumo')
            ->where('cliente_id', $id)
            ->first();

        if (!$cliente) {
            return redirect('/limites-view')
                ->with('error', 'Cliente não encontrado');
        }

        DB::table('cliente_creditos')
            ->where('cliente_id', $id)
            ->update([
                'status' => 'ativo',
                'updated_at' => now()
            ]);

        return redirect('/limites-view')
            ->with('success', "Cliente {$cliente->nome} ,desbloqueado com sucesso!!");
    }
}