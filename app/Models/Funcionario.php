<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Funcionario extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome',
        'funcao',
        'telefone',
        'email',
    ];

    public function usuario()
    {
        return $this->hasOne(Usuario::class);
    }

    public function vendas()
    {
        return $this->hasMany(Venda::class);
    }

    public function entregas()
    {
        return $this->hasMany(Entrega::class);
    }
}
