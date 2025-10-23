<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DevolucaoLog extends Model
{
    use HasFactory;

    protected $table = 'devolucao_logs';

    protected $fillable = [
        'devolucao_id',
        'acao',
        'descricao',
        'usuario',
        'created_at',
        'updated_at',
    ];

    /**
     * Relacionamento com a devolução
     */
    public function devolucao()
    {
        return $this->belongsTo(Devolucao::class, 'devolucao_id');
    }
}
