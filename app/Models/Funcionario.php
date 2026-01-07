<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Funcionario extends Model
{
    use HasFactory;

    // Nome da tabela no banco
    protected $table = 'funcionarios';

    // Campos permitidos para mass assignment
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
    ];

    // Campos que devem ser tratados como datas
    protected $dates = [
        'data_admissao',
        'created_at',
        'updated_at',
    ];

    // Casts para tipos específicos
     protected $casts = [
        'data_admissao' => 'date', // Laravel vai converter para Carbon
        'cpf','nome','funcao','telefone','email',
        'cep','endereco','cidade','estado','numero','bairro',
        'observacoes','data_admissao','ativo'
    ];

    // Enum de funções válidas
    public const FUNCOES = [
        'vendedor',
        'supervisor',
        'motorista',
        'estoquista',
        'operador de caixa',
        'ADM-TI',
        'gerente',
    ];

    /**
     * Relacionamentos úteis
     */

    // Vendas realizadas pelo funcionário
    public function vendas()
    {
        return $this->hasMany(Venda::class, 'funcionario_id');
    }

    // Movimentações de caixa realizadas pelo funcionário
    public function movimentacoesCaixa()
    {
        return $this->hasMany(MovimentacaoCaixa::class, 'user_id');
    }
}
