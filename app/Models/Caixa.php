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
        'empresa_id',   // <<< id da empresa
        'user_id',
        'terminal_id',
        'terminal',
        'valor_fundo_anterior',
        'fundo_troco',
        'fechado_por',
        'divergencia_abertura',
        'valor_abertura',
        'valor_fechamento',
        'data_abertura',
        'data_fechamento',
        'status',
        'observacao',
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

    public function totaisPorFormaPagamento(): array
    {
        $totais = [];

        foreach($this->vendas as $venda) {
            foreach($venda->pagamentos as $pag) {
                if(!isset($totais[$pag->forma_pagamento])) $totais[$pag->forma_pagamento] = 0;
                $totais[$pag->forma_pagamento] += $pag->valor;
            }
        }

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

    // public function verificarSangria(): array
    // {
        
    //     // 💰 Saldo atual baseado em pagamentos_venda
    //     $saldoAtual = $this->saldoDinheiroAtual();

    //     // ⚙️ Busca configuração da empresa
    //     $config = \App\Models\SangriaConfig::where('empresa_id', $this->empresa_id)->first();

    //     // ❌ Sem configuração → não aplica regra
    //     if (!$config) {
    //         return [
    //             'saldoAtual'      => $saldoAtual,
    //             'limiteSangria'   => 0,
    //             'limiteBloqueio'  => 0,
    //             'avisarSangria'   => false,
    //             'bloquearPDV'     => false,
    //         ];
    //     }

    //     // 🎯 Limite principal da sangria
    //     $limiteSangria = (float) $config->valor_limite;

    //     // ⚙️ Configurações adicionais (com default seguro)
    //     $percentualBloqueio = (float) ($config->percentual_bloqueio ?? 50);
    //     $bloqueioAtivo      = (bool) ($config->bloqueio_ativo ?? true);

    //     // 🚧 Limite de bloqueio (ex: 50% acima)
    //     $limiteBloqueio = $limiteSangria * (1 + ($percentualBloqueio / 100));

    //     // 🔔 Regras
    //     $avisarSangria = $saldoAtual >= $limiteSangria;
    //     $bloquearPDV   = $bloqueioAtivo && $saldoAtual >= $limiteBloqueio;

    //     return [
    //         'saldoAtual'      => $saldoAtual,
    //         'limiteSangria'   => $limiteSangria,
    //         'limiteBloqueio'  => $limiteBloqueio,
    //         'avisarSangria'   => $avisarSangria,
    //         'bloquearPDV'     => $bloquearPDV,
    //     ];
    // }

   
    //  public function saldoDinheiroAtual(bool $comLock = false): float
    // {
    //     // Recarrega o caixa com lock opcional para evitar race condition
    //     $query = self::where('id', $this->id)
    //         ->where('status', 'aberto');

    //     if ($comLock) {
    //         $query->lockForUpdate();
    //     }

    //     $caixa = $query->first();

    //     if (!$caixa) {
    //         return 0.00;
    //     }

    //     // Total de vendas em dinheiro confirmadas
    //     $totalVendasDinheiro = DB::table('pagamentos_venda')
    //         ->join('vendas', 'pagamentos_venda.venda_id', '=', 'vendas.id')
    //         ->where('vendas.caixa_id', $caixa->id)
    //         ->where('pagamentos_venda.forma_pagamento', 'dinheiro')
    //         ->where('pagamentos_venda.status', 'confirmado')
    //         ->selectRaw('COALESCE(SUM(pagamentos_venda.valor), 0) as total')
    //         ->value('total');

    //     // Total de sangrias já realizadas
    //     $totalSangrias = DB::table('sangrias')
    //         ->where('caixa_id', $caixa->id)
    //         ->selectRaw('COALESCE(SUM(valor), 0) as total')
    //         ->value('total');

    //     // Fundo de troco seguro
    //     // $fundoTroco = (float) $caixa->fundo_troco;

    //     // Cálculo final
    //     // $saldo = ($fundoTroco + (float) $totalVendasDinheiro) - (float) $totalSangrias;
    //     $saldo = ($totalVendasDinheiro) - (float) $totalSangrias;

    //     // Nunca permitir saldo negativo
    //     return round(max($saldo, 0), 2);
    // }

    public function saldoDinheiroAtual(bool $comLock = false): float
    {
        $query = self::where('id', $this->id)
            ->where('status', 'aberto');

        if ($comLock) {
            $query->lockForUpdate();
        }

        $caixa = $query->first();

        if (!$caixa) {
            return 0.00;
        }

        $totalVendasDinheiro = DB::table('pagamentos_venda as p')
            ->join('vendas as v', 'p.venda_id', '=', 'v.id')
            ->where('v.caixa_id', $caixa->id)
            ->where('p.forma_pagamento', 'dinheiro')
            ->where('p.status', 'confirmado')
            ->sum('p.valor');

        $totalSangrias = DB::table('sangrias')
            ->where('caixa_id', $caixa->id)
            ->sum('valor');

        return round(max($totalVendasDinheiro - $totalSangrias, 0), 2);
    }



    public function verificarSangria(): array
    {
        $empresa = $this->empresa;

        if (!$empresa || !$empresa->configuracaoCaixa) {
            return [
                'saldoAtual'      => 0.0, // Valor fictício para testes
                'limiteSangria'   => 0.0, // Valor fictício para testes
                'limiteBloqueio'  => 0.0,
                'avisarSangria'   => false,
                'bloquearPDV'     => false,
            ];
        }

         // 🔹 Pega a configuração da sangria da empresa ativa
        $config = SangriaConfig::where('empresa_id', $this->empresa_id)->first();
        $config = $empresa->configuracaoCaixa;

        // 🔹 Limite da sangria vindo da tabela
        $limiteSangria = (float) $config->valor_limite;
               
        $limiteSangria        = (float) ($config->limite_sangria ?? 0);
        $percentual    = (float) ($config->percentual_bloqueio ?? 0);
        $bloqueioAtivo = (bool)  ($config->bloqueio_ativo ?? false);
        $saldoAtual = $this->saldoDinheiroAtual();
        $limiteBloqueio = $limiteSangria * (1 + ($percentual / 100));
        
        $deveAvisar   = $saldoAtual >= $limiteSangria;
        $deveBloquear = $bloqueioAtivo && $saldoAtual >= $limiteBloqueio;
        
        return [
            'saldoAtual'     => $saldoAtual,
            'limiteSangria'  => $limiteSangria,
            'limiteBloqueio' => $limiteBloqueio,
            'avisarSangria'  => $deveAvisar,
            'bloquearPDV'    => $deveBloquear,
        ];
    }



// public function verificarSangria(): array
// {
//     // 🔹 Busca a empresa ativa relacionada ao caixa
//     $empresa = \App\Models\Empresa::where('id', $this->empresa_id)
//                 ->where('ativo', 'ativo')
//                 ->first();

//     if (!$empresa) {
//         return [
//             'saldoAtual'      => 0.0,
//             'limiteSangria'   => 0.0,
//             'limiteBloqueio'  => 0.0,
//             'avisarSangria'   => false,
//             'bloquearPDV'     => false,
//         ];
//     }

//     // 🔹 Busca a configuração da sangria para a empresa ativa
//     $config = \App\Models\SangriaConfig::where('empresa_id', $empresa->id)->first();

//     if (!$config) {
//         return [
//             'saldoAtual'      => $this->saldoDinheiroAtual(),
//             'limiteSangria'   => 0.0,
//             'limiteBloqueio'  => 0.0,
//             'avisarSangria'   => false,
//             'bloquearPDV'     => false,
//         ];
//     }

//     $saldoAtual         = $this->saldoDinheiroAtual();
//     $limiteSangria      = (float) $config->valor_limite;
//     $percentualBloqueio = (float) ($config->percentual_bloqueio ?? 50);
//     $bloqueioAtivo      = (bool) ($config->bloqueio_ativo ?? true);
//     $limiteBloqueio     = $limiteSangria * (1 + ($percentualBloqueio / 100));
//     $avisarSangria      = $saldoAtual >= $limiteSangria;
//     $bloquearPDV        = $bloqueioAtivo && $saldoAtual >= $limiteBloqueio;

//     return [
//         'saldoAtual'     => $saldoAtual,
//         'limiteSangria'  => $limiteSangria,
//         'limiteBloqueio' => $limiteBloqueio,
//         'avisarSangria'  => $avisarSangria,
//         'bloquearPDV'    => $bloquearPDV,
//     ];
// }

}
