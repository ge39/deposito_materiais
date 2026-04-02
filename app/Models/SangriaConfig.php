<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SangriaConfig extends Model
{
    use HasFactory; // <-- habilita factory do Laravel

    protected $fillable = [
        'empresa_id',
        'valor_limite',
        'percentual_bloqueio',
        'bloqueio_ativo'
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id', 'id');
    }
    
}