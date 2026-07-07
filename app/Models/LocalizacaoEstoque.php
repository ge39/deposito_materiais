<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LocalizacaoEstoque extends Model
{
    protected $table = 'localizacoes_estoque';

    protected $fillable = [
        'codigo',
        'descricao',
        'tipo_localizacao',
        'setor',
        'rua',
        'lado',
        'modulo',
        'prateleira',
        'nivel',
        'ordem_coleta',
        'ativo',
    ];

    protected $casts = [
        'ordem_coleta' => 'integer',
        'ativo' => 'boolean',
    ];

    public function produtos()
    {
        return $this->hasMany(Produto::class, 'localizacao_estoque_id');
    }

    public function getNomeCompletoAttribute(): string
    {
        return trim($this->codigo . ' - ' . ($this->descricao ?? ''));
    }
}