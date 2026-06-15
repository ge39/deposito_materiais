<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SangriaConfig extends Model
{
    use HasFactory;

    protected $table = 'sangria_configs'; // Garanta que aponta para o nome correto da sua tabela

    protected $fillable = [
        'empresa_id',
        'valor_minimo_caixa',
        'valor_maximo_caixa',
        'valor_limite',
        'sangria_automatica',
        'valor_sangria_automatica',
        'exigir_autorizacao',
        'descricao_padrao'
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id', 'id');
    }
}
