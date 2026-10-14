<?php

namespace App\Console\Commands;

use App\Models\Orcamento;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;

class ExpirarOrcamentos extends Command
{
    protected $signature = 'orcamentos:expirar';

    protected $description = 'Atualiza orcamentos com validade vencida';

    public function handle(): int
    {
        $hoje = now()->format('Y-m-d');

        try {
            $quantidade = Orcamento::where(
                'status',
                'Aguardando aprovacao'
            )
                ->whereDate('validade', '<', $hoje)
                ->update([
                    'status' => 'Expirado',
                    'observacoes' => DB::raw(
                        "CONCAT(IFNULL(observacoes,''), ' | Orçamento expirado em $hoje')"
                    ),
                ]);

            Log::info('ORCAMENTOS_EXPIRACAO_CONCLUIDA', [
                'data' => $hoje,
                'quantidade' => $quantidade,
            ]);

            $this->info("Orcamentos expirados: {$quantidade}");

            return self::SUCCESS;

        } catch (QueryException $e) {

            Log::error('ORCAMENTOS_EXPIRACAO_FALHOU', [
                'data' => $hoje,
                'sqlstate' => $e->errorInfo[0] ?? null,
                'mysql_codigo' => $e->errorInfo[1] ?? null,
            ]);

            $this->error(
                'Falha ao atualizar orcamentos. Consulte o log do ERP.'
            );

            return self::FAILURE;
        }
    }
}