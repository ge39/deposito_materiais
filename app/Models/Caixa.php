<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Caixa extends Model
{
    use HasFactory;

    protected $table = 'caixas';

    protected $fillable = [
        'user_id',
        'terminal_id',
        'terminal',
        'valor_fundo_anterior',
        'fundo_troco',
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

    /* =======================
     * RELACIONAMENTOS
     * ======================= */

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function terminal()
    {
        return $this->belongsTo(Terminal::class, 'terminal_id');
    }

    public function movimentacoes()
    {
        return $this->hasMany(MovimentacaoCaixa::class, 'caixa_id');
    }

    public function vendas()
    {
        return $this->hasMany(Venda::class, 'caixa_id');
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



}
