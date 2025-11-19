<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promocao extends Model
{
    use HasFactory;

    protected $table = 'promocoes';

    protected $fillable = [
        'tipo_abrangencia',
        'produto_id',
        'categoria_id',
        'desconto_percentual',
        'acrescimo_percentual',
        'acrescimo_valor',
        'preco_original',
        'preco_promocional',
        'promocao_inicio',
        'promocao_fim',
        'status',
    ];
   public function promocao()
    {
        return $this->hasOne(Promocao::class, 'produto_id')
            ->where('status', 1); // apenas promoções válidas
    }
    public function unidadeMedida()
    {
        return $this->belongsTo(UnidadeMedida::class, 'unidade_medida');
    }

    public function produto()
    {
        return $this->belongsTo(Produto::class);
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    /**
     * Aplica esta promoção aos produtos relacionados
     */
    public function aplicarPromocao()
    {
        $query = Produto::query();

        if ($this->tipo_abrangencia === 'produto') {
            $query->where('id', $this->produto_id);
        } elseif ($this->tipo_abrangencia === 'categoria') {
            $query->where('categoria_id', $this->categoria_id);
        }

        $produtos = $query->get();

        foreach ($produtos as $produto) {

            // Use o nome correto do campo no seu DB!
            $precoBase = $produto->preco_venda;  

            $preco = $precoBase;

            // Desconto %
            if ($this->desconto_percentual > 0) {
                $preco -= ($preco * $this->desconto_percentual / 100);
            }

            // Acréscimo %
            if ($this->acrescimo_percentual > 0) {
                $preco += ($preco * $this->acrescimo_percentual / 100);
            }

            // Acréscimo fixo
            if ($this->acrescimo_valor > 0) {
                $preco += $this->acrescimo_valor;
            }

            // Se usuário informar preço promocional manual, ele vence qualquer regra
            if ($this->preco_promocional > 0) {
                $preco = $this->preco_promocional;
            }

            // Atualiza o produto
            $produto->update([
                'preco_atual' => $preco,
                'status' => 1,
            ]);
        }
    }
}
