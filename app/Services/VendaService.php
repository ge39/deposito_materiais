<?php

namespace App\Services;

use App\Models\Produto;
use App\Models\Cliente;

class PrecoVendaService
{
    /**
     * Valida se o item do pedido está dentro dos limites de preço e desconto do cliente
     */
    public function validarItemPedido(Cliente $cliente, Produto $produto, float $precoCobrado, float $percentualDesconto): array
    {
        // 1. Determina as colunas corretas baseado no ENUM do cliente
        switch ($cliente->tipo_cliente) {
            case 'markup_2':
                // Se preço_venda_2 for nulo ou zero, assume o preco_venda padrão
                $precoBase = (float) (!empty($produto->preco_venda_2) ? $produto->preco_venda_2 : $produto->preco_venda);
                $descontoMaximo = (float) ($produto->desconto_max_2 ?? 0);
                break;

            case 'markup_3':
                // Se preço_venda_3 for nulo ou zero, assume o preco_venda padrão
                $precoBase = (float) (!empty($produto->preco_venda_3) ? $produto->preco_venda_3 : $produto->preco_venda);
                $descontoMaximo = (float) ($produto->desconto_max_3 ?? 0);
                break;

            case 'markup_1':
            case 'normal':  // Proteção contra registros antigos/padrão do banco
            case 'balcao':  // Proteção contra registros antigos/padrão do banco
            default:
                $precoBase = (float) $produto->preco_venda;
                $descontoMaximo = (float) ($produto->desconto_max_1 ?? 0);
                break;
        }

        // 2. Realiza as checagens de segurança
        if ($percentualDesconto > $descontoMaximo) {
            return [
                'valido' => false,
                'mensagem' => "O desconto solicitado de {$percentualDesconto}% ultrapassa o limite máximo permitido de {$descontoMaximo}% para este perfil de cliente."
            ];
        }

        // Calcula o menor preço possível aceitável para o item após o desconto de tabela
        $precoMinimoPermitido = $precoBase * (1 - ($descontoMaximo / 100));

        // Arredonda para 2 casas decimais para evitar bugs de precisão de ponto flutuante do PHP
        $precoMinimoPermitido = round($precoMinimoPermitido, 2);
        $precoCobrado = round($precoCobrado, 2);

        if ($precoCobrado < $precoMinimoPermitido) {
            return [
                'valido' => false,
                'mensagem' => "O preço unitário inserido (R$ " . number_format($precoCobrado, 2, ',', '.') . ") está abaixo do preço mínimo permitido por tabela para este produto (R$ " . number_format($precoMinimoPermitido, 2, ',', '.') . ")."
            ];
        }

        return [
            'valido' => true,
            'markup_aplicado' => $cliente->tipo_cliente,
            'preco_base' => $precoBase,
            'desconto_maximo' => $descontoMaximo
        ];
    }
}
