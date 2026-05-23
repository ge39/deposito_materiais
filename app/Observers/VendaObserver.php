<?php 

namespace App\Observers; 

use App\Models\Venda; 
use App\Services\BloqueioCreditoService; 

class VendaObserver 
{ 
    // public function saved(Venda $venda): void 
    // { 
    //     // 🔥 CLÁUSULA DE SALVAGUARDA: Se for Venda Balcão (null), ignora a reavaliação de crédito
    //     if (is_null($venda->cliente_id)) {
    //         return;
    //     }

    //     $cliente = $venda->cliente; 
    //     app(BloqueioCreditoService::class)->reavaliarCliente($cliente); 
    // } 
    
    public function saved(Venda $venda): void 
    { 
        // 1. Ignora se o ID for nulo (Venda Balcão Padrão)
        if (is_null($venda->cliente_id)) {
            return;
        }

        // 2. Ignora se for o ID específico do cadastro de "Venda Balcão" do seu banco
        // Substitua o '1' pelo ID real do seu cliente balcão, se aplicável
        if ($venda->cliente_id === 1) { 
            return;
        }

        $cliente = $venda->cliente; 
        app(BloqueioCreditoService::class)->reavaliarCliente($cliente); 
    } 


    public function deleted(Venda $venda): void 
    { 
        // 🔥 CLÁUSULA DE SALVAGUARDA: Se for Venda Balcão (null), ignora a reavaliação de crédito
        if (is_null($venda->cliente_id)) {
            return;
        }

        $cliente = $venda->cliente; 
        app(BloqueioCreditoService::class)->reavaliarCliente($cliente); 
    } 
}
