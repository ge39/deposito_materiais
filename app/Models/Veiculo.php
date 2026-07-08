<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Veiculo extends Model
{
    protected $table = 'veiculos';

    protected $fillable = [
        'placa',
        'modelo',
        'marca',
        'ano_fabricacao',
        'cor',
        'chassi',
        'renavam',
        'tipo',
        'motorista_padrao_id',
        'categoria_cnh',
        'capacidade_kg',
        'capacidade_m3',
        'capacidade_unidades',
        'capacidade_paletes',
        'comprimento_m',
        'largura_m',
        'altura_m',
        'possui_munck',
        'possui_carroceria_aberta',
        'possui_carroceria_fechada',
        'possui_rastreador',
        'aceita_areia_pedra',
        'aceita_blocos_tijolos',
        'aceita_cimento_argamassa',
        'aceita_tintas_quimicos',
        'aceita_telhas',
        'aceita_madeiras',
        'restricao_rodizio',
        'restricao_zona_central',
        'restricao_altura',
        'restricao_peso',
        'consumo_medio_km_l',
        'custo_medio_km',
        'km_atual',
        'km_ultima_revisao',
        'data_ultima_revisao',
        'data_proxima_revisao',
        'vencimento_documento',
        'vencimento_seguro',
        'vencimento_ipva',
        'status',
        'disponibilidade',
        'prioridade_uso',
        'ativo',
        'observacao',
    ];

    protected $casts = [
        'possui_munck' => 'boolean',
        'possui_carroceria_aberta' => 'boolean',
        'possui_carroceria_fechada' => 'boolean',
        'possui_rastreador' => 'boolean',
        'aceita_areia_pedra' => 'boolean',
        'aceita_blocos_tijolos' => 'boolean',
        'aceita_cimento_argamassa' => 'boolean',
        'aceita_tintas_quimicos' => 'boolean',
        'aceita_telhas' => 'boolean',
        'aceita_madeiras' => 'boolean',
        'restricao_rodizio' => 'boolean',
        'restricao_zona_central' => 'boolean',
        'restricao_altura' => 'boolean',
        'restricao_peso' => 'boolean',
        'ativo' => 'boolean',

        'capacidade_kg' => 'decimal:2',
        'capacidade_m3' => 'decimal:2',
        'comprimento_m' => 'decimal:2',
        'largura_m' => 'decimal:2',
        'altura_m' => 'decimal:2',
        'consumo_medio_km_l' => 'decimal:2',
        'custo_medio_km' => 'decimal:2',

        'data_ultima_revisao' => 'date',
        'data_proxima_revisao' => 'date',
        'vencimento_documento' => 'date',
        'vencimento_seguro' => 'date',
        'vencimento_ipva' => 'date',
    ];

    public function motoristaPadrao()
    {
        return $this->belongsTo(Funcionario::class, 'motorista_padrao_id');
    }
}