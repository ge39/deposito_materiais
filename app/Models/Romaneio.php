<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Romaneio extends Model
{
    protected $table = 'romaneios';

    protected $fillable = [
        'entrega_id',
        'criado_por',
        'codigo_romaneio',
        'token_abertura',
        'token_fechamento',
        'Cancelado',
        'status',

        'veiculo_id',
        'motorista_id',

        'iniciado_por',
        'carregado_por',
        'conferido_por',
        'finalizado_por',

        'data_emissao',
        'impresso_em',
        'impresso_por',

        'data_inicio_separacao',
        'data_fim_separacao',

        'data_inicio_conferencia_separacao',
        'data_fim_conferencia_separacao',
        'conferencia_separacao_iniciada_por',
        'conferencia_separacao_finalizada_por',

        'data_inicio_carregamento',
        'data_fim_carregamento',

        'data_inicio_conferencia_saida',
        'data_fim_conferencia_saida',
        'conferencia_saida_iniciada_por',
        'conferencia_saida_finalizada_por',

        'data_saida',
        'data_retorno',
        'retorno_registrado_por',

        'data_inicio_prestacao_contas',
        'data_fim_prestacao_contas',
        'prestacao_contas_por',

        'data_baixa',

        'fechado_em',
        'fechado_por',
        'metodo_fechamento',
        'justificativa_fechamento_manual',

        'percentual_carregado',

        'observacao',
        'motivo_cancelamento',
        'cancelado_em',
        'cancelado_por',
    ];

    protected $casts = [
        'data_emissao' => 'datetime',
        'impresso_em' => 'datetime',

        'data_inicio_separacao' => 'datetime',
        'data_fim_separacao' => 'datetime',

        'data_inicio_conferencia_separacao' => 'datetime',
        'data_fim_conferencia_separacao' => 'datetime',

        'data_inicio_carregamento' => 'datetime',
        'data_fim_carregamento' => 'datetime',

        'data_inicio_conferencia_saida' => 'datetime',
        'data_fim_conferencia_saida' => 'datetime',

        'data_saida' => 'datetime',
        'data_retorno' => 'datetime',

        'data_inicio_prestacao_contas' => 'datetime',
        'data_fim_prestacao_contas' => 'datetime',

        'data_baixa' => 'datetime',
        'fechado_em' => 'datetime',
        'cancelado_em' => 'datetime',

        'percentual_carregado' => 'decimal:2',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relacionamentos principais
    |--------------------------------------------------------------------------
    */

    public function entrega(): BelongsTo
    {
        return $this->belongsTo(
            Entrega::class,
            'entrega_id'
        );
    }

    public function itens(): HasMany
    {
        return $this->hasMany(
            RomaneioItem::class,
            'romaneio_id'
        )->orderBy('ordem');
    }

    public function veiculo(): BelongsTo
    {
        return $this->belongsTo(
            Veiculo::class,
            'veiculo_id'
        );
    }

    public function motorista(): BelongsTo
    {
        return $this->belongsTo(
            Funcionario::class,
            'motorista_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Usuários responsáveis por ações no sistema
    |--------------------------------------------------------------------------
    */

    public function criador(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'criado_por'
        );
    }

    public function iniciador(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'iniciado_por'
        );
    }

    public function finalizador(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'finalizado_por'
        );
    }

    public function impressor(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'impresso_por'
        );
    }

    public function usuarioInicioConferenciaSeparacao(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'conferencia_separacao_iniciada_por'
        );
    }

    public function usuarioFimConferenciaSeparacao(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'conferencia_separacao_finalizada_por'
        );
    }

    public function usuarioInicioConferenciaSaida(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'conferencia_saida_iniciada_por'
        );
    }

    public function usuarioFimConferenciaSaida(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'conferencia_saida_finalizada_por'
        );
    }

    public function usuarioRegistroRetorno(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'retorno_registrado_por'
        );
    }

    public function usuarioPrestacaoContas(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'prestacao_contas_por'
        );
    }

    public function usuarioFechamento(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'fechado_por'
        );
    }

    public function cancelador(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'cancelado_por'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Funcionários responsáveis pela execução física
    |--------------------------------------------------------------------------
    */

    public function carregador(): BelongsTo
    {
        return $this->belongsTo(
            Funcionario::class,
            'carregado_por'
        );
    }

    public function conferente(): BelongsTo
    {
        return $this->belongsTo(
            Funcionario::class,
            'conferido_por'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Equipe operacional
    |--------------------------------------------------------------------------
    */

    public function equipes(): HasMany
    {
        return $this->hasMany(
            RomaneioEquipe::class,
            'romaneio_id'
        );
    }

    public function equipeAtiva(): HasOne
    {
        return $this->hasOne(
            RomaneioEquipe::class,
            'romaneio_id'
        )->where('status', 'Ativa');
    }

    /*
    |--------------------------------------------------------------------------
    | Ocorrências e auditoria
    |--------------------------------------------------------------------------
    */

    public function ocorrencias(): HasMany
    {
        return $this->hasMany(
            RomaneioOcorrencia::class,
            'romaneio_id'
        );
    }

    public function eventos(): HasMany
    {
        return $this->hasMany(
            RomaneioEvento::class,
            'romaneio_id'
        )->orderBy('ocorrido_em');
    }

    /*
    |--------------------------------------------------------------------------
    | Regras auxiliares do domínio
    |--------------------------------------------------------------------------
    */

    public function possuiOcorrenciaCriticaAberta(): bool
    {
        return $this->ocorrencias()
            ->where('criticidade', 'Critico')
            ->whereIn('status', [
                'Aberta',
                'Em_analise',
            ])
            ->exists();
    }

    public function possuiOcorrenciaBloqueante(): bool
    {
        return $this->ocorrencias()
            ->where('bloqueia_operacao', true)
            ->whereIn('status', [
                'Aberta',
                'Em_analise',
            ])
            ->exists();
    }

    public function possuiOcorrenciaAguardandoAutorizacao(): bool
    {
        return $this->ocorrencias()
            ->where('exige_autorizacao', true)
            ->whereNull('autorizada_por')
            ->whereIn('status', [
                'Aberta',
                'Em_analise',
            ])
            ->exists();
    }

    public function prestacaoContasConciliada(): bool
    {
        return ! $this->itens()
            ->get()
            ->contains(
                fn (RomaneioItem $item): bool =>
                    ! $item->prestacaoContasConciliada()
            );
    }

    public function podeSerFechado(): bool
    {
        if ($this->possuiOcorrenciaBloqueante()) {
            return false;
        }

        if ($this->possuiOcorrenciaAguardandoAutorizacao()) {
            return false;
        }

        if (! $this->prestacaoContasConciliada()) {
            return false;
        }

        return in_array(
            $this->status,
            [
                'Aguardando_fechamento',
                'Em_prestacao_contas',
            ],
            true
        );
    }

    public function estaFechado(): bool
    {
        return $this->status === 'Fechado';
    }

    public function estaCancelado(): bool
    {
        return $this->status === 'Cancelado';
    }
}