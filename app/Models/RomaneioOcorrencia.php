<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class RomaneioOcorrencia extends Model
{
    protected $table = 'romaneio_ocorrencias';

    protected $fillable = [
        'romaneio_id',
        'romaneio_equipe_id',
        'entrega_id',
        'romaneio_item_id',
        'entrega_item_id',
        'quantidade_envolvida',
        'categoria',
        'tipo',
        'classificacao_inicial',
        'classificacao_final',
        'criticidade',
        'etapa',
        'descricao',
        'bloqueia_operacao',
        'exige_autorizacao',
        'autorizada_por',
        'autorizada_em',
        'justificativa_autorizacao',
        'status',
        'triagem_status',
        'condicao_triagem',
        'destino_sugerido',
        'justificativa_triagem',
        'grupo_triagem_uuid',
        'triado_por',
        'triado_em',
        'registrada_por',
        'registrada_em',
        'assumida_por',
        'assumida_em',
        'responsavel_analise_id',
        'prazo_analise_em',
        'analise_iniciada_em',
        'decidida_por',
        'decidida_em',
        'decisao',
        'destino_estoque',
        'movimentacao_estoque_id',
        'orcamento_reposicao_id',
        'permite_fechamento_logistico',
        'resolvida_por',
        'resolvida_em',
        'solucao',
    ];

    protected $casts = [
        'quantidade_envolvida' => 'decimal:3',
        'bloqueia_operacao' => 'boolean',
        'exige_autorizacao' => 'boolean',
        'permite_fechamento_logistico' => 'boolean',
        'autorizada_em' => 'datetime',
        'triado_em' => 'datetime',
        'registrada_em' => 'datetime',
        'assumida_em' => 'datetime',
        'prazo_analise_em' => 'datetime',
        'analise_iniciada_em' => 'datetime',
        'decidida_em' => 'datetime',
        'resolvida_em' => 'datetime',
    ];

    public function romaneio(): BelongsTo
    {
        return $this->belongsTo(Romaneio::class, 'romaneio_id');
    }

    public function equipe(): BelongsTo
    {
        return $this->belongsTo(RomaneioEquipe::class, 'romaneio_equipe_id');
    }

    public function entrega(): BelongsTo
    {
        return $this->belongsTo(Entrega::class, 'entrega_id');
    }

    public function romaneioItem(): BelongsTo
    {
        return $this->belongsTo(RomaneioItem::class, 'romaneio_item_id');
    }

    public function entregaItem(): BelongsTo
    {
        return $this->belongsTo(EntregaItem::class, 'entrega_item_id');
    }

    public function autorizador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'autorizada_por');
    }

    public function triadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'triado_por');
    }

    public function registrador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrada_por');
    }

    public function assumidaPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assumida_por');
    }

    public function responsavelAnalise(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsavel_analise_id');
    }

    public function decididaPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decidida_por');
    }

    public function resolvedor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolvida_por');
    }

    public function orcamentoReposicao(): BelongsTo
    {
        return $this->belongsTo(Orcamento::class, 'orcamento_reposicao_id');
    }

    public function anexos(): HasMany
    {
        return $this->hasMany(
            RomaneioOcorrenciaAnexo::class,
            'romaneio_ocorrencia_id'
        );
    }

    public function historicos(): HasMany
    {
        return $this->hasMany(
            RomaneioOcorrenciaHistorico::class,
            'romaneio_ocorrencia_id'
        )->orderBy('registrado_em');
    }

    public function avaliacoes(): HasMany
    {
        return $this->hasMany(
            RomaneioOcorrenciaAvaliacao::class,
            'romaneio_ocorrencia_id'
        )->orderBy('ordem');
    }

    public function produtoExtraviado(): HasOne
    {
        return $this->hasOne(
            ProdutoExtraviado::class,
            'romaneio_ocorrencia_id'
        );
    }

    public function devolucao(): HasOne
    {
        return $this->hasOne(
            Devolucao::class,
            'romaneio_ocorrencia_id'
        );
    }

    public function estaAberta(): bool
    {
        return in_array(
            $this->status,
            [
                'Aberta',
                'Aguardando_evidencias',
                'Aguardando_responsavel',
                'Em_analise',
                'Aguardando_decisao',
                'Aprovada_troca',
                'Aprovada_devolucao',
                'Extravio_confirmado',
            ],
            true
        );
    }

    public function estaResolvida(): bool
    {
        return $this->status === 'Resolvida';
    }

    public function estaCancelada(): bool
    {
        return $this->status === 'Cancelada';
    }

    public function triagemConcluida(): bool
    {
        return $this->triagem_status === 'Concluida';
    }

    public function pertenceAoGrupoTriagem(): bool
    {
        return ! empty($this->grupo_triagem_uuid);
    }

    public function exigeAutorizacaoPendente(): bool
    {
        return $this->exige_autorizacao && empty($this->autorizada_por);
    }

    public function possuiResponsavel(): bool
    {
        return ! empty($this->responsavel_analise_id);
    }

    public function possuiEvidencias(): bool
    {
        if ($this->relationLoaded('anexos')) {
            return $this->anexos->isNotEmpty();
        }

        return $this->anexos()->exists();
    }

    public function possuiAvaliacoes(): bool
    {
        if ($this->relationLoaded('avaliacoes')) {
            return $this->avaliacoes->isNotEmpty();
        }

        return $this->avaliacoes()->exists();
    }

    public function quantidadeAvaliada(): float
    {
        if ($this->relationLoaded('avaliacoes')) {
            return round(
                (float) $this->avaliacoes->sum('quantidade'),
                3
            );
        }

        return round(
            (float) $this->avaliacoes()->sum('quantidade'),
            3
        );
    }

    public function avaliacaoQuantidadeConferida(): bool
    {
        return abs(
            $this->quantidadeAvaliada()
            - (float) $this->quantidade_envolvida
        ) < 0.001;
    }

    public function podeLiberarFechamentoLogistico(): bool
    {
        return
            $this->triagemConcluida()
            && $this->possuiAvaliacoes()
            && $this->avaliacaoQuantidadeConferida()
            && $this->possuiResponsavel()
            && $this->possuiEvidencias()
            && ! $this->exigeAutorizacaoPendente();
    }

    public function bloqueiaFluxo(): bool
    {
        return
            $this->estaAberta()
            && (
                ! $this->triagemConcluida()
                || ! $this->possuiAvaliacoes()
                || ! $this->avaliacaoQuantidadeConferida()
                || $this->bloqueia_operacao
                || $this->exigeAutorizacaoPendente()
            );
    }
}