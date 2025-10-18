<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Funcionario extends Model
{
    use HasFactory;

    // Nome da tabela no banco (evita problemas de pluralização)
    protected $table = 'funcionarios';

    // Campos que podem ser preenchidos via mass assignment
    protected $fillable = [
        'nome',
        'funcao',
        'telefone',
        'email',
        'cpf',
        'endereco',
        'cidade',
        'observacoes',
        'data_admissao'
    ];
     protected $dates = [
        'created_at',
        'updated_at',
    ];
    protected $casts = [
    'data_admissao' => 'date', // Laravel vai converter para Carbon
    ];

}
