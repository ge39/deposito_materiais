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


    // public function verificarSangria(): array 
    // {
    //     // Busca a configuração da sangria ativa para a empresa deste caixa
    //     $config = \App\Models\SangriaConfig::where('empresa_id', $this->empresa_id)->first();

    //     if (!$config) {
    //         return [
    //             'saldoAtual' => 0.0,
    //             'limiteSangria' => 0.0,
    //             'limiteBloqueio' => 0.0,
    //             'avisarSangria' => false,
    //             'bloquearPDV' => false,
    //         ];
    //     }

    //     $limite = (float) $config->valor_limite; // Agora vai ler os seus 200 configurados
    //     $percentual = (float) ($config->percentual_bloqueio ?? 0); 
    //     $bloqueioAtivo = (bool) ($config->bloqueio_ativo ?? true); 

    //     $saldoAtual = (float) $this->saldoDinheiroAtual(); // Pega o saldo em dinheiro deste caixa específico
    //     $valorSugeridoSangria = max(0, $saldoAtual - $limite);
        
    //     // Se houver percentual de bloqueio ex: 50% em cima de 200 = limite de bloqueio vira 300
    //     $limiteBloqueio = $limite * (1 + ($percentual / 100)); 

    //     return [
    //         'saldoAtual' => $saldoAtual,
    //         'limiteSangria' => $limite,
    //         'limiteBloqueio' => $limiteBloqueio,
    //         'avisarSangria' => $saldoAtual >= $limite,
    //         'bloquearPDV' => $bloqueioAtivo && $saldoAtual >= $limiteBloqueio,
    //         'valorSugeridoSangria' => $valorSugeridoSangria,
    //     ];
    // }

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

