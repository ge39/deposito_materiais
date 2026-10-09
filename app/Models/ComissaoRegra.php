<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComissaoRegra extends Model
{
    protected $table = 'comissao_regras';

    protected $fillable = [
        'funcionario_id',
        'escopo',
        'categoria_id',
        'produto_id',
        'usar_percentual_produto',
        'percentual',
        'vigencia_inicio',
        'vigencia_fim',
        'ativo',
        'observacao',
    ];

    protected $casts = [
        'usar_percentual_produto' => 'boolean',
        'percentual' => 'decimal:4',
        'vigencia_inicio' => 'date',
        'vigencia_fim' => 'date',
        'ativo' => 'boolean',
    ];

    public function funcionario(): BelongsTo
    {
        return $this->belongsTo(Funcionario::class, 'funcionario_id');
    }

    public function produto(): BelongsTo
    {
        return $this->belongsTo(Produto::class, 'produto_id');
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }
}