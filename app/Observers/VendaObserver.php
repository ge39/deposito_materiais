<?php 

namespace App\Observers; 

use App\Models\Venda; 
use App\Services\BloqueioCreditoService; 

class VendaObserver 
{ 
    /**
     * 🔥 EVITA TRAVAMENTOS (DEADLOCKS)
     * Garante que as consultas pesadas de crédito só rodem DEPOIS 
     * que a transação da venda for confirmada (commit) no banco.
     */
    public $afterCommit = true; 

    public function saved(Venda $venda): void 
    { 
        // 1. CLÁUSULA DE SALVAGUARDA: Ignora se for Venda Balcão padrão (ID nulo)
        if (is_null($venda->cliente_id)) {
            return;
        }

        // 2. CLÁUSULA DE SALVAGUARDA 2: Caso seu sistema use um ID fixo para Venda Balcão no banco (Ex: ID 1)
        // Se aplicável, mude o número abaixo para o ID real do seu cliente balcão genérico
        if ($venda->cliente_id === 1) {
            return;
        }

        $cliente = $venda->cliente; 
        app(BloqueioCreditoService::class)->reavaliarCliente($cliente); 
    } 

    public function deleted(Venda $venda): void 
    { 
        // Aplica as mesmas travas de segurança na exclusão de uma venda
        if (is_null($venda->cliente_id) || $venda->cliente_id === 1) {
            return;
        }

        $cliente = $venda->cliente; 
        app(BloqueioCreditoService::class)->reavaliarCliente($cliente); 
    } 
}
