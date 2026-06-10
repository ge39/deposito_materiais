<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Cliente;
use App\Services\CreditoService;

class VerificarClientesAtrasados extends Command
{
    // Assinatura do comando para execução no terminal
    protected $signature = 'credito:verificar-atrasos';
    protected $description = 'Varre a base de clientes bloqueando as carteiras com parcelas vencidas.';

    protected $creditoService;

    public function __construct(CreditoService $creditoService)
    {
        parent::__construct();
        $this->creditoService = $creditoService;
    }

    public function handle()
    {
        $this->info('Iniciando checagem diária de inadimplência...');

        // Traz apenas clientes que possuem crédito ativo e não estão bloqueados ainda
        Cliente::whereHas('creditoAtivo', function ($query) {
            $query->where('status', 'ativo');
        })->chunk(100, function ($clientes) {
            foreach ($clientes as $cliente) {
                // Executa o método de checagem com Join que implementamos no seu CreditoService
                $bloqueou = $this->creditoService->verificarEBloquearPorAtraso($cliente);
                
                if ($bloqueou) {
                    $this->warn("Cliente ID #{$cliente->id} ({$cliente->nome}) foi bloqueado por parcelas vencidas.");
                }
            }
        });

        $this->info('Checagem concluída com sucesso!');
        return Command::SUCCESS;
    }
}
