<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClienteCredito extends Model
{
    protected $table = 'cliente_creditos';

    protected $fillable = [
        'cliente_id',
        'limite_credito',
        'status',
    ];

    protected $casts = [
        'limite_credito' => 'decimal:2',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }
    
    public function contaCorrente()
    {
        return $this->hasOne(ClienteContaCorrente::class, 'cliente_id');
    }
}