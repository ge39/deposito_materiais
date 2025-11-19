<?php

namespace App\Services;

use App\Models\Promocao;
use App\Models\Produto;
use Illuminate\Support\Facades\DB;

class PromocaoService
{
    // Aplica a promoção deixando PREÇO ORIGINAL salvo apenas uma vez
    public function aplicar(Promocao $promocao)
    {
        $produto = $promocao->produto;

        if (!$produto) {
            return;
        }

        // Salva o preço original da primeira vez
        if (!$promocao->preco_original) {
            $promocao->update([
                'preco_original' => $produto->preco_venda
            ]);
        }

        // Regras de cálculo
        $novoPreco = $produto->preco_venda;

        if ($promocao->desconto_percentual > 0) {
            $novoPreco -= ($novoPreco * ($promocao->desconto_percentual / 100));
        }

        if ($promocao->acrescimo_valor > 0) {
            $novoPreco += $promocao->acrescimo_valor;
        }

        if ($promocao->preco_promocional > 0) {
            $novoPreco = $promocao->preco_promocional;
        }

        $produto->update([
            'preco_venda' => max($novoPreco, 0.01),
            'status' => true,
        ]);
    }

    // Restaura o produto ao preço original
    public function restaurar(Promocao $promocao)
    {
        $produto = $promocao->produto;

        if ($produto && $promocao->preco_original) {
            $produto->update([
                'preco_venda' => $promocao->preco_original,
                'status' => false,
            ]);
        }
    }

    // 🔥 Verifica promoções expiradas e restaura automaticamente
    public function restaurarSeExpirada()
    {
        // Promoções com status = 1 e que já passaram da data final
        $expiradas = Promocao::where('status', 1)
            ->whereDate('promocao_fim', '<', now()->toDateString())
            ->get();

        foreach ($expiradas as $promocao) {
            DB::transaction(function () use ($promocao) {

                // Marca como encerrada
                $promocao->update([
                    'status' => 0,
                ]);

                // Restaura o preço original do produto
                $this->restaurar($promocao);
            });
        }
    }
}
