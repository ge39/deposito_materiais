<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Empresa extends Model
{
    protected $table = 'empresa';

    protected $fillable = [
        'nome',
        'cnpj',
        'inscricao_estadual',
        'endereco',
        'numero',
        'complemento',
        'bairro',
        'cidade',
        'estado',
        'cep',
        'latitude',
        'longitude',
        'telefone',
        'email',
        'site',
        'ativo',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'ativo' => 'boolean',
    ];

    public function veiculos()
    {
        return $this->hasMany(
            Veiculo::class,
            'empresa_id',
            'id'
        );
    }

    public static function ativa()
    {
        return self::where('ativo', 1)->first();
    }

    public function configuracaoCaixa(): HasOne
    {
        return $this->hasOne(
            ConfiguracoesCaixa::class,
            'empresa_id',
            'id'
        );
    }

    public function rastreamentoConfig(): HasOne
    {
        return $this->hasOne(
            EmpresaRastreamentoConfig::class,
            'empresa_id',
            'id'
        );
    }

    public function sangriaConfig()
    {
        return $this->hasOne(SangriaConfig::class);
    }

    public function orcamentos()
    {
        return $this->hasMany(Orcamento::class);
    }
}