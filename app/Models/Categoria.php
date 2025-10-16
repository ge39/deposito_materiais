<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    protected $fillable = [
        'nome',
        'descricao',
    ];

    // Se futuramente quiser relacionar com produtos:
    public function produtos()
    {
        return $this->hasMany(Produto::class, 'categoria_id');
    }
}
