public function store(Request $request)
{
    // Verifica se existe vale aplicado
    $vale = null;
    $valorDesconto = 0;

    if ($request->filled('vale_codigo')) {
        $vale = \App\Models\ValeCompra::where('codigo', $request->vale_codigo)
            ->where('status', 'ativo')
            ->first();

        if (!$vale) {
            return back()->with('error', 'Código de vale inválido ou expirado.');
        }

        // Verifica se o vale pertence ao mesmo cliente (opcional)
        if ($vale->cliente_id != $request->cliente_id) {
            return back()->with('error', 'Este vale não pertence ao cliente selecionado.');
        }

        $valorDesconto = min($vale->saldo, $request->total_venda);
    }

    // Cria a venda normalmente
    $venda = Venda::create([
        'cliente_id' => $request->cliente_id,
        'data_venda' => now(),
        'valor_total' => $request->total_venda - $valorDesconto,
        'status' => 'concluída',
    ]);

    // Se houver vale, atualiza os dados
    if ($vale) {
        $vale->valor_utilizado += $valorDesconto;

        if ($vale->saldo <= 0.01) {
            $vale->status = 'usado';
            $vale->data_utilizacao = now();
        }

        $vale->save();
    }

    return redirect()->route('vendas.show', $venda->id)
        ->with('success', 'Venda concluída com sucesso' . ($vale ? ' com uso de vale!' : ''));
}
