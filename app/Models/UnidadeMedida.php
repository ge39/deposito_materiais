<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnidadeMedida extends Model
{
    use HasFactory;

    // Corrige o nome da tabela
    protected $table = 'unidades_medida';

    protected $fillable = ['nome', 'sigla', 'ativo'];

    public function produtos()
    {
        return $this->hasMany(Produto::class);
    }
}
