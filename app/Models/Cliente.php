<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome',
        'tipo_cliente',
        'cpf_cnpj',
        'rg_ie',
        'telefone',
        'email',
        'endereco',
        'limite_credito',
        'observacoes',
    ];

    public function vendas()
    {
        return $this->hasMany(Venda::class);
    }
}
