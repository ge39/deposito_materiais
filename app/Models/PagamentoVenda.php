<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PagamentoVenda extends Model
{
    use HasFactory;

    protected $table = 'pagamentos_venda';

    protected $fillable = [
        'user_id',
        'venda_id',
        'forma_pagamento',
        'bandeira',
        'valor',
        'parcelas',
        'status',
    ];

    protected static function booted()
    {
        $bloquearSeCaixaFechado = function ($pagamento, $mensagem) {

            $pagamento->loadMissing('venda.caixa');

            if (!$pagamento->venda || !$pagamento->venda->caixa) {
                return;
            }

            if (in_array($pagamento->venda->caixa->status, ['fechado','inconsistente'])) {
                throw new \Exception($mensagem);
            }
        };

        static::creating(function ($pagamento) use ($bloquearSeCaixaFechado) {
            $bloquearSeCaixaFechado(
                $pagamento,
                'Não é permitido adicionar pagamentos a um caixa já fechado.'
            );
        });

        static::updating(function ($pagamento) use ($bloquearSeCaixaFechado) {
            $bloquearSeCaixaFechado(
                $pagamento,
                'Não é permitido alterar pagamentos de um caixa já fechado.'
            );
        });

        static::deleting(function ($pagamento) use ($bloquearSeCaixaFechado) {
            $bloquearSeCaixaFechado(
                $pagamento,
                'Não é permitido excluir pagamentos de um caixa já fechado.'
            );
        });
    }

   public function venda()
    {
        return $this->belongsTo(\App\Models\Venda::class, 'venda_id');
    }

}
