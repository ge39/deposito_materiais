<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Venda extends Model
{
    use HasFactory;

    protected $table = 'vendas';

    protected $fillable = [
        'caixa_id',
        'cliente_id',
        'funcionario_id',
        'total',
        'status',
    ];

    protected $dates = ['created_at','updated_at','data_venda'];

    protected $casts = [
        'data_venda' => 'datetime',
    ];

    /* Relacionamentos */

    public function itens()
    {
        return $this->hasMany(ItemVenda::class, 'venda_id');
    }

    public function pagamentos()
    {
        return $this->hasMany(PagamentoVenda::class, 'venda_id');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function caixa()
    {
        return $this->belongsTo(Caixa::class, 'caixa_id');
    }
    
    public function funcionario()
    {
        return $this->belongsTo(Funcionario::class, 'funcionario_id');
    }

}
