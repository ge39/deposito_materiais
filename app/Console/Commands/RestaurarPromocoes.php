<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Promocao;
use App\Models\Produto;

class RestaurarPromocoes extends Command
{
    protected $signature = 'promocoes:restaurar';
    protected $description = 'Restaura os preços originais quando a promoção expira';

    public function handle()
    {
        $promocoes = Promocao::where('em_promocao', 1)
            ->where('promocao_fim', '<', now())
            ->get();

        foreach ($promocoes as $promo) {

            if ($promo->tipo_abrangencia === 'produto') {

                $produto = Produto::find($promo->produto_id);

                if ($produto) {
                    $produto->update([
                        'preco_venda' => $produto->preco_original_produto
                    ]);
                }
            }

            if ($promo->tipo_abrangencia === 'categoria') {

                $produtos = Produto::where('categoria_id', $promo->categoria_id)->get();

                foreach ($produtos as $p) {
                    $p->update([
                        'preco_venda' => $p->preco_original_produto
                    ]);
                }
            }

            $promo->update(['em_promocao' => 0]);
        }

        $this->info('Promoções restauradas com sucesso!');
    }
}
