<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Terminal extends Model
{
    use HasFactory;

    protected $table = 'terminais';

    protected $fillable = [
        'uuid',
        'identificador',
        'descricao',
        'user_agent',
        'primeiro_acesso',
        'ultimo_acesso',
        'ativo',
    ];

    protected $casts = [
        'ativo'           => 'boolean',
        'primeiro_acesso' => 'datetime',
        'ultimo_acesso'   => 'datetime',
    ];
}
