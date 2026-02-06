<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovimentacaoCaixa extends Model
{
    use HasFactory;

    protected $table = 'movimentacoes_caixa';

    protected $fillable = [
        'caixa_id',
        'user_id',
        'tipo',
        'valor',
        'valor_auditado',  // novo
        'forma_pagamento',   // novo
        'bandeira',          // novo
        'origem_id',         // novo
        'observacao',
        'data_movimentacao',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'valor_auditado' => 'decimal:2', // ✅
        'data_movimentacao' => 'datetime',
    ];

    // protected static function booted()
    // {
    //     static::saving(function ($mov) {
    //         if ($mov->valor_auditado !== null) {
    //             if ($mov->tipo !== 'fechamento') {
    //                 throw new \Exception(
    //                     'Somente fechamento pode ter valor auditado.'
    //                 );
    //             }

    //             if (empty($mov->forma_pagamento)) {
    //                 throw new \Exception(
    //                     'Auditoria exige forma de pagamento.'
    //                 );
    //             }
    //         }
    //     });
    // }

    protected static function booted()
    {
        // static::saving(function ($mov) {

        //     // valor_auditado só pode existir em auditoria
        //     if ($mov->valor_auditado !== null) {

        //         if ($mov->tipo !== 'auditoria') {
        //             throw new \Exception(
        //                 'Valor auditado só pode ser usado em movimentações de auditoria.'
        //             );
        //         }

        //         if (empty($mov->forma_pagamento)) {
        //             throw new \Exception(
        //                 'Auditoria exige forma de pagamento.'
        //             );
        //         }
        //     }
        // });

        
        static::saving(function ($mov) {

            if ($mov->tipo === 'fechamento') {
                if (is_null($mov->valor_auditado)) {
                    throw new \DomainException(
                        'Fechamento exige valor_auditado (total das vendas).'
                    );
                }
            }

            if ($mov->tipo === 'auditoria') {
                if (empty($mov->forma_pagamento)) {
                    throw new \DomainException(
                        'Auditoria exige forma de pagamento.'
                    );
                }
            }

            if (!in_array($mov->tipo, ['fechamento', 'auditoria']) 
                && !is_null($mov->valor_auditado)) {
                throw new \DomainException(
                    'valor_auditado só é permitido em fechamento ou auditoria.'
                );
            }
        });

    }

    //Se quiser que data_movimentacao seja tratada como Carbon
    // protected $dates = ['created_at','updated_at','data_movimentacao'];

    public $timestamps = true;

    /* =========================
       RELACIONAMENTOS
       ========================= */

    public function caixa()
    {
        return $this->belongsTo(Caixa::class, 'caixa_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Venda relacionada (quando tipo = venda ou cancelamento_venda)
     * Usa origem_id
     */
    public function venda()
    {
        return $this->belongsTo(Venda::class, 'origem_id');
    }

    // vincular uma movimentação a uma venda, sangria ou ajuste:
    public function origem()
    {
        return $this->morphTo(); // ou belongsTo dependendo do caso
    }   

}
