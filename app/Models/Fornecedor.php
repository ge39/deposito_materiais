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
        'endereco',
        'cidade',
        'observacoes',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];
}
