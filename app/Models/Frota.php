<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Frota extends Model
{
    use HasFactory;

    protected $fillable = [
        'veiculo',
        'placa',
        'capacidade',
    ];

    public function entregas()
    {
        return $this->hasMany(Entrega::class);
    }
}
