<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ClienteContaCorrente;
use App\Models\ClienteCredito;

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

    
//    public function contaCorrente()
//     {
//         return $this->hasMany(ClienteContaCorrente::class, 'cliente_id');
//     }

    public function ultimaMovimentacao()
    {
        return $this->hasOne(ClienteContaCorrente::class, 'cliente_id')
            ->latestOfMany();
    }

    //  public function creditoAtivo()
    // {
    //     return $this->hasOne(\App\Models\ClienteCredito::class, 'cliente_id')
    //                 ->where('status', 'ativo');
    // }

    public function creditoAtivo()
    {
        return $this->hasOne(ClienteCredito::class, 'cliente_id');
    }

    public function credito()
    {
        return $this->hasOne(ClienteCredito::class, 'cliente_id');
    }

}


   
