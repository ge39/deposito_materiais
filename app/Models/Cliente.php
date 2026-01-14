<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;

    protected $table = 'clientes';

    protected $fillable = ['id',
        'nome','tipo','data_nascimento','sexo','cpf_cnpj','rg_ie','orgao_emissor','data_emissao',
        'telefone','email','cep','endereco','numero','bairro','cidade','estado','endereco_entrega',
        'limite_credito','observacoes','ativo'
    ];

    protected $casts = [
        'data_nascimento' => 'date',
        'data_emissao' => 'date',
        'limite_credito' => 'decimal:2',
    ];


     public function vendas()
    {
        return $this->hasMany(Venda::class);
    }

}


   
