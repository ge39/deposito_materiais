<?php

namespace App\Models;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Empresa;
use App\Models\Venda;
use App\Models\SangriaConfig;


class Caixa extends Model
{
    use HasFactory;

    protected $table = 'caixas';

    protected $fillable = [
        'empresa_id',           // 👈 Crucial para multi-empresas
        'user_id',
        'terminal_id',
        'terminal',
        'valor_fundo_anterior',
        'fundo_troco',
        'valor_abertura',       // 👈 Adicionado para bater com seu banco
        'fechado_por',
        'divergencia_abertura',
        'status',
        'observacao',
        'data_abertura',
    ];

    protected $casts = [
        'valor_fundo_anterior'   => 'decimal:2',
        'fundo_troco'            => 'decimal:2',
        'divergencia_abertura'   => 'decimal:2',
        'valor_abertura'         => 'decimal:2',
        'valor_fechamento'       => 'decimal:2',
        'data_abertura'          => 'datetime',
        'data_fechamento'        => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($caixa) {
            if (!$caixa->empresa_id && auth()->user()) {
                $caixa->empresa_id = auth()->user()->empresa_id;
            }
        });
    }

    /* =======================
     * RELACIONAMENTOS
     * ======================= */
    public function usuario() { return $this->belongsTo(User::class, 'user_id'); }
    public function terminal()  { return $this->belongsTo(Terminal::class, 'terminal_id'); }
    public function movimentacoes() { return $this->hasMany(MovimentacaoCaixa::class, 'caixa_id'); }
    public function vendas()  { return $this->hasMany(Venda::class, 'caixa_id'); }
    public function empresa() { return $this->belongsTo(Empresa::class, 'empresa_id', 'id'); }
    
    
    public function possuiVendas(): bool
    {
        return $this->vendas()
            ->whereHas('pagamentos', function ($q) {
                $q->where('status', 'confirmado');
            })
            ->exists();
    }

    
    public function isBloqueado(): bool
    {
        $verificacao = $this->verificarSangria();
        return $verificacao['bloquearPDV'];
    }

    public function desbloquear()
    {
        // Aqui pode ser um campo 'bloqueado' no caixa
        $this->update(['bloqueado' => false]);
    }

    
    /* =======================
     * SCOPES ÚTEIS
     * ======================= */

    public function scopeAbertos($query)
    {
        return $query->where('status', 'aberto');
    }

    public function scopeDoTerminal($query, $terminalId)
    {
        return $query->where('terminal_id', $terminalId);
    }

    /* =======================
     * MÉTODOS DE NEGÓCIO
     * ======================= */

    public function estaAberto(): bool
    {
        return $this->status === 'aberto';
    }
    public function totalEsperado(): float
    {
        $entradas = $this->movimentacoes->whereIn('tipo', ['abertura', 'venda', 'entrada_manual'])->sum('valor');
        $saidas   = $this->movimentacoes->whereIn('tipo', ['saida_manual', 'cancelamento_venda'])->sum('valor');
        return $this->valor_abertura + $entradas - $saidas;
    }

    // public function totaisPorFormaPagamento(): array
    // {
    //     $totais = [];

    //     foreach($this->vendas as $venda) {
    //         foreach($venda->pagamentos as $pag) {
    //             if(!isset($totais[$pag->forma_pagamento])) $totais[$pag->forma_pagamento] = 0;
    //             $totais[$pag->forma_pagamento] += $pag->valor;
    //         }
    //     }

    //     return $totais;
    // }

    public function totaisPorFormaPagamento(): array
    {
        $totais = [
            'dinheiro'       => 0.00,
            'pix'            => 0.00,
            'carteira'       => 0.00,
            'cartao_debito'  => 0.00,
            'cartao_credito' => 0.00,
        ];

        // Percorre todas as vendas e soma os valores nas suas respectivas formas de pagamento
        foreach ($this->vendas as $venda) {
            foreach ($venda->pagamentos as $pag) {
                // Converte a string para minúsculo para evitar problemas de case-sensitive (ex: 'Carteira' vs 'carteira')
                $forma = strtolower($pag->forma_pagamento);
                
                if (!isset($totais[$forma])) {
                    $totais[$forma] = 0.00;
                }
                $totais[$forma] += (float) $pag->valor;
            }
        }

        // 🎯 O TOTAL DO SISTEMA CONTÁBIL: Soma todas as chaves do array de formas de pagamento
        $totais['total_sistema'] = array_sum($totais);

        return $totais;
    }

    public function divergencia(float $valorFisico): float
    {
        return $valorFisico - $this->totalEsperado();
    }

    public function sangrias()
    {
        return $this->hasMany(MovimentacaoCaixa::class, 'caixa_id')
                    ->where('tipo', 'sangria');
    }


    //aqui verifica o valor que vai verificar o saldo para sangria
//    public function saldoDinheiroAtual(bool $comLock = false): float
//     {
//         $query = self::where('id', $this->id)
//             ->where('status', 'aberto');

//         if ($comLock) {
//             $query->lockForUpdate();
//         }

//         $caixa = $query->first();

//         if (!$caixa) {
//             return 0.00;
//         }

//         // 1️⃣ Soma TODAS as entradas em dinheiro registradas para este caixa (vendas, entradas manuais, suprimentos)
//         // O seu registro (tipo: 'venda', forma_pagamento: 'dinheiro', valor: 1050) entrará exatamente aqui.
//         $totalEntradasDinheiro = DB::table('movimentacoes_caixa')
//             ->where('caixa_id', $caixa->id)
//             ->where('forma_pagamento', 'dinheiro')
//             ->whereIn('tipo', ['venda', 'entrada_manual', 'entrada'])
//             ->sum('valor');

//         // 2️⃣ Soma TODAS as saídas em dinheiro efetuadas (sangrias antigas, saídas manuais, despesas)
//         $totalSaidasDinheiro = DB::table('movimentacoes_caixa')
//             ->where('caixa_id', $caixa->id)
//             ->where('forma_pagamento', 'dinheiro')
//             ->whereIn('tipo', ['sangria', 'saida_manual', 'saida', 'despesa', 'ajuste_negativo'])
//             ->sum('valor');

//         // 🎯 O saldo acumulado vivo no caixa é: Entradas menos Saídas (Fundo de troco isolado fora da sangria)
//         $saldoParaSangria = $totalEntradasDinheiro - $totalSaidasDinheiro;

//         return round(max($saldoParaSangria, 0), 2);
//     }

   public function saldoDinheiroAtual(): float
    {
        // 1. Soma absolutamente tudo o que colocou DINHEIRO EM ESPÉCIE na gaveta
        $entradas = \Illuminate\Support\Facades\DB::table('movimentacoes_caixa')
            ->where('caixa_id', $this->id)
            ->where('forma_pagamento', 'dinheiro') // Garante focar apenas em espécie
            ->whereIn('tipo', [
                'abertura', 
                'venda', 
                'entrada', 
                'aporte', 
                'entrada_manual', 
                'entrada_pagto_carteira' // 🎯 String idêntica à gravada no banco de dados
            ])
            ->sum('valor');

        // 2. Soma as saídas manuais e despesas administrativas registradas em dinheiro
        $saidasManuais = \Illuminate\Support\Facades\DB::table('movimentacoes_caixa')
            ->where('caixa_id', $this->id)
            ->where('forma_pagamento', 'dinheiro')
            ->whereIn('tipo', ['saida_manual', 'cancelamento_venda', 'saida', 'despesa'])
            ->sum('valor');

        // 3. Busca pelas sangrias registradas na fita de auditoria
        $sangriasPelasMovimentacoes = \Illuminate\Support\Facades\DB::table('movimentacoes_caixa')
            ->where('caixa_id', $this->id)
            ->whereIn('tipo', ['sangria', 'Saida_manual']) 
            ->sum('valor');

        // 4. Busca também direto na tabela física de sangrias para garantir o abatimento seguro
        $sangriasPelaTabelaFisica = \Illuminate\Support\Facades\DB::table('sangrias')
            ->where('caixa_id', $this->id)
            ->sum('valor');

        // Captura o maior índice de sangria encontrado para evitar furos
        $totalSangrias = max($sangriasPelasMovimentacoes, $sangriasPelaTabelaFisica);

        // MATEMÁTICA FINAL DA GAVETA
        $saldoReal = $entradas - ($saidasManuais + $totalSangrias);

        return (float) max(0, $saldoReal);
    }


    public function verificarSangria(): array
    {
        // 1. Obtém o saldo real em dinheiro deste caixa específico
        $saldoAtual = $this->saldoDinheiroAtual();

        // 2. Busca a configuração amarrada à empresa deste caixa
        $config = \App\Models\SangriaConfig::where('empresa_id', $this->empresa_id)->first();

        if (!$config) {
            $config = \App\Models\SangriaConfig::first();
        }

        if (!$config) {
            return [
                'saldoAtual' => $saldoAtual,
                'limiteSangria' => 200.00,
                'limiteBloqueio' => 200.00,
                'avisarSangria' => false,
                'bloquearPDV' => false,
                'valorSugeridoSangria' => 200.00,
            ];
        }

        $limiteSangria = (float) ($config->valor_limite ?? 200.00); 
        $limiteBloqueio = (float) ($config->valor_maximo_caixa ?? $limiteSangria); 

        $deveAvisar = ($saldoAtual >= $limiteSangria) && ($saldoAtual > 0);
        $deveBloquear = ($saldoAtual >= $limiteBloqueio) && ($saldoAtual > 0);

        // 🎯 REGRA ATUALIZADA: Se atingiu o limite, sugere sangrar o valor cheio configurado (R$ 200)
        // Se o saldo for menor que o limite, sugere 0.
        $valorSugeridoSangria = $saldoAtual >= $limiteSangria ? $limiteSangria : 0.00;

        return [
            'saldoAtual' => $saldoAtual,
            'limiteSangria' => $limiteSangria,
            'limiteBloqueio' => $limiteBloqueio,
            'avisarSangria' => $deveAvisar,
            'bloquearPDV' => $deveBloquear,
            'valorSugeridoSangria' => round($valorSugeridoSangria, 2), // 👈 Retorna os 200.00 perfeitamente
        ];
    }

}

