<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promocao extends Model
{
    use HasFactory;

    // Nome correto da tabela no banco
    protected $table = 'promocoes';
    
    protected $fillable = [
        'tipo_abrangencia',
        'produto_id',
        'categoria_id',
        'desconto_percentual',
        'acrescimo_percentual',
        'acrescimo_valor',
        'preco_promocional',
        'promocao_inicio',
        'promocao_fim',
        'em_promocao',
    ];

    public function produto()
    {
        return $this->belongsTo(Produto::class);
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }
    public function aplicarPromocao(Promocao $promocao)
{
    $query = Produto::query();

    if ($promocao->tipo_abrangencia === 'produto') {
        $query->where('id', $promocao->produto_id);
    } elseif ($promocao->tipo_abrangencia === 'categoria') {
        $query->where('categoria_id', $promocao->categoria_id);
    }

    $produtos = $query->get();

    foreach ($produtos as $produto) {
        $precoBase = $produto->preco_base;

        $preco = $precoBase;

        if ($promocao->desconto_percentual > 0) {
            $preco -= ($preco * $promocao->desconto_percentual / 100);
        }

        if ($promocao->acrescimo_percentual > 0) {
            $preco += ($preco * $promocao->acrescimo_percentual / 100);
        }

        if ($promocao->acrescimo_valor > 0) {
            $preco += $promocao->acrescimo_valor;
        }

        if ($promocao->preco_promocional) {
            $preco = $promocao->preco_promocional;
        }

        $produto->update([
            'preco_atual' => $preco,
            'em_promocao' => true,
        ]);
    }
}

}
