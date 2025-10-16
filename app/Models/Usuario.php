<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Usuario extends Model
{
    use HasFactory;

    protected $fillable = [
        'funcionario_id',
        'nome_usuario',
        'email',
        'senha',
        'nivel',
        'status',
    ];

    protected $hidden = ['senha'];

    public function funcionario()
    {
        return $this->belongsTo(Funcionario::class);
    }
}
