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
        'tipo_veiculo_id',
        'classe_veiculo_id',
        'tipo_carroceria_id',

        'tipo_frota',
        'disponibilidade',
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
        'aceita_madeiras',
        'aceita_ferragens',
        'aceita_pisos_revestimentos',
        'aceita_hidraulica_eletrica',

        'status',
        'observacao',
    ];

    protected $casts = [
        'ano_fabricacao' => 'integer',

        'tipo_veiculo_id' => 'integer',
        'classe_veiculo_id' => 'integer',
        'tipo_carroceria_id' => 'integer',
        'motorista_padrao_id' => 'integer',

        'capacidade_kg' => 'decimal:2',
        'capacidade_m3' => 'decimal:2',
        'capacidade_unidades' => 'integer',
        'capacidade_paletes' => 'integer',

        'comprimento_m' => 'decimal:2',
        'largura_m' => 'decimal:2',
        'altura_m' => 'decimal:2',

        'possui_munck' => 'boolean',
        'possui_carroceria_aberta' => 'boolean',
        'possui_carroceria_fechada' => 'boolean',
        'possui_rastreador' => 'boolean',

        'aceita_areia_pedra' => 'boolean',
        'aceita_blocos_tijolos' => 'boolean',
        'aceita_cimento_argamassa' => 'boolean',
        'aceita_tintas_quimicos' => 'boolean',
        'aceita_madeiras' => 'boolean',
        'aceita_ferragens' => 'boolean',
        'aceita_pisos_revestimentos' => 'boolean',
        'aceita_hidraulica_eletrica' => 'boolean',
    ];

    public function motoristaPadrao()
    {
        return $this->belongsTo(Funcionario::class, 'motorista_padrao_id');
    }

    public function tipoVeiculo()
    {
        return $this->belongsTo(TipoVeiculo::class, 'tipo_veiculo_id');
    }

    public function classeVeiculo()
    {
        return $this->belongsTo(ClasseVeiculo::class, 'classe_veiculo_id');
    }

    public function tipoCarroceria()
    {
        return $this->belongsTo(TipoCarroceria::class, 'tipo_carroceria_id');
    }
}