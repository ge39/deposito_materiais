<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sangria extends Model
{
    use HasFactory;

    protected $table = 'sangrias';

    protected $fillable = [
        'empresa_id',
        'caixa_id',
        'user_id',
        'codigo_operacao',
        'numero_pdv',
        'valor',
        'saldo_antes',
        'saldo_depois',
        'motivo',
        'impresso',
        'impresso_em'
    ];

    // Relacionamentos
    public function caixa()
    {
        return $this->belongsTo(Caixa::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function saldoDinheiroEntradas(): float
    {
        return $this->movimentacoes()
            ->where('forma_pagamento', 'dinheiro')
            ->whereIn('tipo', ['entrada_manual', 'entrada', 'venda'])
            ->sum('valor');
    }

    public function saldoDinheiroSaidas(): float
    {
        return $this->movimentacoes()
            ->where('forma_pagamento', 'dinheiro')
            ->whereIn('tipo', ['sangria','despesa','ajuste_negativo','outras_saidas'])
            ->sum('valor');
    }
}