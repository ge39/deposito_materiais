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

    protected static function booted()
    {
        $bloquearSeCaixaFechado = function ($venda, $mensagem) {

            $venda->loadMissing('caixa');

            if (!$venda->caixa) {
                return;
            }

            if (in_array($venda->caixa->status, ['fechado','inconsistente'])) {
                throw new \Exception($mensagem);
            }
        };

        static::updating(function ($venda) use ($bloquearSeCaixaFechado) {
            $bloquearSeCaixaFechado(
                $venda,
                'Não é permitido alterar vendas de um caixa já fechado.'
            );
        });

        static::deleting(function ($venda) use ($bloquearSeCaixaFechado) {
            $bloquearSeCaixaFechado(
                $venda,
                'Não é permitido excluir vendas de um caixa já fechado.'
            );
        });
    }

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
    return $this->belongsTo(\App\Models\Cliente::class, 'cliente_id');
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
