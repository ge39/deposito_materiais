<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
class Empresa extends Model
{
    protected $table = 'empresa'; // Nome da tabela
    protected $fillable = [
        'nome',
        'cnpj',
        'inscricao_estadual',
        'endereco',
        'numero',
        'complemento',
        'bairro',
        'cidade',
        'estado',
        'cep',
        'telefone',
        'email',
        'site',
        'ativo'
    ];
    
    public function configuracaoCaixa(): HasOne
    {
        return $this->hasOne(ConfiguracoesCaixa::class, 'empresa_id', 'id');
    }
}
