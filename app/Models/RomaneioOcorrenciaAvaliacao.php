<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RomaneioOcorrenciaAvaliacao extends Model
{
    protected $table = 'romaneio_ocorrencia_avaliacoes';

    public const EMBALAGENS = [
        'Intacta',
        'Rasgada',
        'Aberta',
        'Furada',
        'Amassada',
        'Quebrada',
        'Lacre_violado',
        'Sem_embalagem',
        'Nao_se_aplica',
    ];

    public const CONTEUDOS = [
        'Preservado',
        'Parcial',
        'Vazando',
        'Derramado',
        'Espalhado',
        'Endurecido',
        'Molhado',
        'Misturado',
        'Contaminado',
        'Ausente',
        'Nao_se_aplica',
    ];

    public const INTEGRIDADES = [
        'Integro',
        'Dano_leve',
        'Dano_parcial',
        'Quebrado',
        'Deformado',
        'Incompleto',
        'Perda_total',
        'Avaliacao_inconclusiva',
    ];

    public const STATUS_VALIDADE = [
        'Dentro_validade',
        'Proximo_vencimento',
        'Vencido',
        'Data_ilegivel',
        'Sem_identificacao',
        'Nao_se_aplica',
    ];

    public const ORIGENS_VALIDADE = [
        'Lote',
        'Produto',
        'Manual',
        'Nao_aplicavel',
    ];

    public const REAPROVEITAMENTOS = [
        'Uso_normal',
        'Reembalagem',
        'Troca_embalagem',
        'Reparo_embalagem',
        'Venda_granel',
        'Venda_com_avaria',
        'Uso_interno',
        'Aproveitamento_parcial',
        'Retorno_fornecedor',
        'Reciclagem',
        'Sem_reaproveitamento',
    ];

    public const DESTINOS = [
        'Sem_movimentacao',
        'Quarentena',
        'Reintegracao',
        'Perda',
        'Reposicao',
    ];

    protected $fillable = [
        'romaneio_ocorrencia_id',
        'lote_id',
        'ordem',
        'quantidade',
        'embalagem',
        'conteudo',
        'integridade',
        'validade_status',
        'validade_referencia',
        'origem_validade',
        'reaproveitamento',
        'destino_sugerido',
        'observacao',
        'avaliado_por',
        'avaliado_em',
    ];

    protected $casts = [
        'ordem' => 'integer',
        'quantidade' => 'decimal:3',
        'validade_referencia' => 'date',
        'avaliado_em' => 'datetime',
    ];

    public function ocorrencia(): BelongsTo
    {
        return $this->belongsTo(
            RomaneioOcorrencia::class,
            'romaneio_ocorrencia_id'
        );
    }

    public function lote(): BelongsTo
    {
        return $this->belongsTo(
            Lote::class,
            'lote_id'
        );
    }

    public function avaliadoPor(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'avaliado_por'
        );
    }
}