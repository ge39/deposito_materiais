<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClienteContaCorrente extends Model
{
    protected $table = 'cliente_conta_correntes';

    protected $fillable = [
        'cliente_id',
        'venda_id',
        'pagamento_venda_id',
        'tipo',
        'origem',
        'valor',
        'saldo_apos',
        'descricao'
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function venda()
    {
        return $this->belongsTo(Venda::class, 'venda_id');
    }

    public function pagamentoVenda()
    {
        return $this->belongsTo(PagamentoVenda::class, 'pagamento_venda_id');
    }
}