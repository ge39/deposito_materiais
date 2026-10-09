<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComissaoVenda extends Model
{
    protected $table = 'comissoes_vendas';

    protected $fillable = [
        'venda_id',
        'item_venda_id',
        'funcionario_id',
        'produto_id',
        'regra_comissao_id',
        'percentual_aplicado',
        'valor_base',
        'valor_comissao',
        'status',
        'data_competencia',
        'gerada_em',
        'aprovada_em',
        'paga_em',
        'estornada_em',
        'observacao',
    ];

    protected $casts = [
        'percentual_aplicado' => 'decimal:4',
        'valor_base' => 'decimal:2',
        'valor_comissao' => 'decimal:2',
        'data_competencia' => 'date',
        'gerada_em' => 'datetime',
        'aprovada_em' => 'datetime',
        'paga_em' => 'datetime',
        'estornada_em' => 'datetime',
    ];

    public function funcionario(): BelongsTo
    {
        return $this->belongsTo(Funcionario::class, 'funcionario_id');
    }

    public function venda(): BelongsTo
    {
        return $this->belongsTo(Venda::class, 'venda_id');
    }

    public function produto(): BelongsTo
    {
        return $this->belongsTo(Produto::class, 'produto_id');
    }

    public function regra(): BelongsTo
    {
        return $this->belongsTo(
            ComissaoRegra::class,
            'regra_comissao_id'
        );
    }
}