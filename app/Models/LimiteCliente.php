<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LimiteCliente extends Model
{
    protected $table = 'vw_limite_clientes';

    protected $primaryKey = 'cliente_id';

    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'cliente_id',
        'nome',
        'limite_credito',
        'total_movimentado',
        'saldo_atual',
        'limite_disponivel',
        'limite_usado',
        'status_limite'
    ];
}