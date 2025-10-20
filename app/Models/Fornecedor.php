<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fornecedor extends Model
{
    use HasFactory;

    protected $table = 'fornecedores';

    protected $fillable = [
        'nome',
        'cnpj',
        'telefone',
        'email',
        'cep',
        'rua',
        'numero',
        'bairro',
        'cidade',
        'estado',
        'observacoes',
        'ativo',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];
}
