<?php 

namespace App\Observers; 

use App\Models\Venda; 
use App\Services\BloqueioCreditoService; 

class VendaObserver 
{ 
    public function saved(Venda $venda): void 
    { 
        // 🔥 CLÁUSULA DE SALVAGUARDA: Se for Venda Balcão (null), ignora a reavaliação de crédito
        if (is_null($venda->cliente_id)) {
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
