<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Funcionario extends Model
{
    use HasFactory;

    protected $table = 'funcionarios';

    protected $fillable = [
        'nome',
        'cpf',
        'funcao',
        'telefone',
        'salario',
        'email',
        'cep',
        'endereco',
        'numero',
        'bairro',
        'cidade',
        'estado',
        'observacoes',
        'data_admissao',
        'ativo',
        'rastreamento_habilitado',
    ];

    protected $casts = [
        'data_admissao' => 'date',
        'ativo' => 'boolean',
        'rastreamento_habilitado' => 'boolean',
        'localizacao_consentida_em' => 'datetime',
        'localizacao_revogada_em' => 'datetime',
    ];

    public const FUNCOES = [
        'vendedor',
        'supervisor',
        'ajudante_motorista',
        'ajudante_geral',
        'motorista',
        'estoquista',
        'operador de caixa',
        'ADM-TI',
        'gerente',
    ];

    public const FUNCOES_LABELS = [
        'vendedor' => 'Vendedor',
        'supervisor' => 'Supervisor',
        'ajudante_motorista' => 'Ajudante de motorista',
        'ajudante_geral' => 'Ajudante geral',
        'motorista' => 'Motorista',
        'estoquista' => 'Estoquista',
        'operador de caixa' => 'Operador de caixa',
        'ADM-TI' => 'ADM-TI',
        'gerente' => 'Gerente',
    ];

    public function scopeMotoristas($query)
    {
        return $query->where('funcao', 'motorista');
    }

    public function scopeAtivos($query)
    {
        return $query->where('ativo', 1);
    }

    public function podeCompartilharLocalizacao(): bool
    {
        return $this->funcao === 'motorista'
            && $this->ativo
            && $this->rastreamento_habilitado;
    }

    public function vendas()
    {
        return $this->hasMany(Venda::class, 'funcionario_id');
    }

    public function movimentacoesCaixa()
    {
        return $this->hasMany(MovimentacaoCaixa::class, 'user_id');
    }

    public function entregasComoMotorista()
    {
        return $this->hasMany(Entrega::class, 'motorista_id');
    }
}