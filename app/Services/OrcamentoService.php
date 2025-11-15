<?php

namespace App\Services;

use App\Models\Orcamento;
use App\Models\ItemOrcamento;
use App\Models\Produto;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class OrcamentoService
{
    /**
     * LISTAGEM
     */
    public function listar($request)
    {
        $query = Orcamento::with('cliente');

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->codigo_orcamento) {
            $query->where('codigo_orcamento', $request->codigo_orcamento);
        }

        return $query->orderBy('id', 'desc')->paginate(15);
    }

    /**
     * MARCAR EDIÇÃO
     */
    public function marcarEdicao(Orcamento $orcamento)
    {
        if ($orcamento->editando_por && $orcamento->editando_por != Auth::id()) {
            return false;
        }

        $orcamento->update([
            'editando_por' => Auth::id(),
            'editando_em'  => now()
        ]);

        return true;
    }

    /**
     * LIMPAR EDIÇÃO
     */
    public function limparEdicao(Orcamento $orcamento)
    {
        $orcamento->update([
            'editando_por' => null,
            'editando_em'  => null
        ]);

        return true;
    }

    /**
     * CRIAÇÃO DO ORÇAMENTO
     */
    public function criar($data)
    {
        return DB::transaction(function () use ($data) {

            $orcamento = Orcamento::create([
                'cliente_id' => $data['cliente_id'],
                'data_orcamento' => $data['data_orcamento'],
                'codigo_orcamento' => now()->format('YmdHis'),
                'validade' => $data['validade'],
                'status' => 'Aguardando aprovacao',
                'observacoes' => $data['observacoes'] ?? null,
                'total' => 0,
                'ativo' => 1,
            ]);

            $codigo = now()->format('Ymd') . $orcamento->id;
            $orcamento->update(['codigo_orcamento' => $codigo]);

            $total = 0;
            $produtoIds = [];

            foreach ($data['produtos'] as $item) {

                if (in_array($item['id'], $produtoIds)) {
                    throw new \Exception("Produto duplicado no orçamento.");
                }

                $produtoIds[] = $item['id'];

                $subtotal = $item['quantidade'] * $item['preco_unitario'];

                ItemOrcamento::create([
                    'orcamento_id' => $orcamento->id,
                    'produto_id' => $item['id'],
                    'quantidade' => $item['quantidade'],
                    'preco_unitario' => $item['preco_unitario'],
                    'subtotal' => $subtotal
                ]);

                $total += $subtotal;
            }

            $orcamento->update(['total' => $total]);

            return $orcamento;
        });
    }

    /**
     * ATUALIZAÇÃO DO ORÇAMENTO
     */
    public function atualizar(Orcamento $orcamento, $data)
    {
        return DB::transaction(function () use ($orcamento, $data) {

            if ($orcamento->status == 'Expirado') {
                $orcamento->update(['status' => 'Aguardando aprovacao']);
            }

            $orcamento->update([
                'cliente_id' => $data['cliente_id'],
                'data_orcamento' => $data['data_orcamento'],
                'validade' => $data['validade'],
                'observacoes' => $data['observacoes'] ?? null,
            ]);

            $orcamento->itens()->delete();

            $total = 0;

            foreach ($data['produtos'] as $item) {

                $subtotal = $item['quantidade'] * $item['preco_unitario'];

                ItemOrcamento::create([
                    'orcamento_id' => $orcamento->id,
                    'produto_id' => $item['id'],
                    'quantidade' => $item['quantidade'],
                    'preco_unitario' => $item['preco_unitario'],
                    'subtotal' => $subtotal
                ]);

                $total += $subtotal;
            }

            $orcamento->update([
                'total' => $total,
                'editando_por' => null,
                'editando_em' => null,
            ]);

            return $orcamento;
        });
    }

    /**
     * EXCLUSÃO
     */
    public function excluir(Orcamento $orcamento)
    {
        if ($orcamento->status === 'Aprovado') {
            throw new \Exception("Não é possível excluir orçamentos aprovados.");
        }

        return $orcamento->delete();
    }

    /**
     * APROVAR
     */
    public function aprovar(Orcamento $orcamento)
    {
        $orcamento->update(['status' => 'Aprovado']);
        return $orcamento;
    }

    /**
     * CANCELAR
     */
    public function cancelar(Orcamento $orcamento)
    {
        $orcamento->update(['status' => 'Cancelado']);
        return $orcamento;
    }

    /**
     * REATIVAR
     */
    public function reativar(Orcamento $orcamento)
    {
        if ($orcamento->status !== 'Expirado') {
            throw new \Exception("Este orçamento não está expirado.");
        }

        $orcamento->update(['status' => 'Aguardando Aprovação']);
        return $orcamento;
    }

    /**
     * GERAR PDF
     */
    public function gerarPdf(Orcamento $orcamento)
    {
        $orcamento->load('cliente', 'itens.produto.unidadeMedida');

        return Pdf::loadView('orcamentos.pdf', compact('orcamento'));
    }

    /**
     * ENVIAR WHATSAPP
     */
    public function enviarWhatsapp(Orcamento $orcamento)
    {
        $pdf = Pdf::loadView('orcamentos.pdf', compact('orcamento'));
        $fileName = "orcamento_{$orcamento->codigo_orcamento}.pdf";

        $path = storage_path("app/public/orcamento/{$fileName}");
        $pdf->save($path);

        $telefone = preg_replace('/\D/', '', $orcamento->cliente->telefone ?? '');

        if (!$telefone) {
            throw new \Exception("Cliente não possui telefone cadastrado.");
        }

        $link = asset("storage/orcamento/{$fileName}");
        $mensagem = urlencode("Olá! Segue seu orçamento: {$link}");

        return "https://wa.me/55{$telefone}?text={$mensagem}";
    }

    /**
     * VISUALIZAR PDF
     */
    public function visualizarArquivo(Orcamento $orcamento)
    {
        $fileName = "orcamento_{$orcamento->codigo_orcamento}.pdf";
        return asset("storage/orcamento/{$fileName}");
    }
}
