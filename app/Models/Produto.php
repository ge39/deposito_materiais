<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Carbon\Carbon;

class Produto extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome',
        'codigo_barras',
        'sku',
        'descricao',
        'categoria_id',
        'fornecedor_id',
        'unidade_medida_id',
        'marca_id',
        'quantidade_estoque',
        'estoque_total',
        'estoque_minimo',
        'preco_custo',
        'preco_venda',
        'preco_base',
        'preco_promocional',
        'desconto_percentual',
        'promocao_inicio',
        'promocao_fim',
        'em_promocao',
        'data_compra',
        'validade_produto',
        'peso',
        'largura',
        'altura',
        'profundidade',
        'localizacao_estoque',
        'imagem',
        'ativo',
    ];

    protected $casts = [
        'data_compra' => 'date',
        'validade_produto' => 'date',
        'preco_custo' => 'decimal:2',
        'preco_venda' => 'decimal:2',
        'preco_base' => 'decimal:2',
        'preco_promocional' => 'decimal:2',
        'promocao_inicio' => 'date',
        'promocao_fim' => 'date',
        'em_promocao' => 'boolean',
    ];

    // -------------------------------
    // RELACIONAMENTOS
    // -------------------------------
    public function lotes()
    {
        return $this->hasMany(Lote::class, 'produto_id');
    }

    public function unidadeMedida()
    {
        return $this->belongsTo(UnidadeMedida::class, 'unidade_medida_id');
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function fornecedor()
    {
        return $this->belongsTo(Fornecedor::class);
    }

    public function marca()
    {
        return $this->belongsTo(Marca::class);
    }

    // -------------------------------
    // ESTOQUE
    // -------------------------------
    public function getEstoqueTotalAttribute()
    {
        return $this->lotes->sum('quantidade');
    }

    public function atualizarEstoqueTotal()
    {
        $this->estoque_total = $this->lotes()->sum('quantidade');
        $this->saveQuietly();
    }

    // -------------------------------
    // MUTATORS
    // -------------------------------
    public function setValidadeProdutoAttribute($value)
    {
        $this->attributes['validade_produto'] = empty($value) ? null : Carbon::parse($value)->startOfDay();
    }

    public function getNomeFormatadoAttribute()
    {
        return Str::title($this->nome);
    }

    public function getPrecoMedioCompraAttribute()
    {
        $lotes = $this->lotes;

        if ($lotes->isEmpty()) {
            return 0;
        }

        $totalValor = 0;
        $totalQuantidade = 0;

        foreach ($lotes as $lote) {
            $totalValor += $lote->quantidade * $lote->preco_compra;
            $totalQuantidade += $lote->quantidade;
        }

        return $totalQuantidade > 0 ? round($totalValor / $totalQuantidade, 2) : 0;
    }

    // -------------------------------
    // ACCESSOR: PREÇO ATUAL (com promo/desconto)
    // -------------------------------
    public function getPrecoAtualAttribute()
    {
        $preco = $this->preco_base ?? $this->preco_venda;
        $hoje = Carbon::today();

        if ($this->em_promocao &&
            $this->promocao_inicio && $this->promocao_fim &&
            $hoje->between(Carbon::parse($this->promocao_inicio), Carbon::parse($this->promocao_fim))) {

            if ($this->preco_promocional) {
                $preco = $this->preco_promocional;
            } elseif ($this->desconto_percentual) {
                $preco = $preco * (1 - $this->desconto_percentual / 100);
            }
        }

        return round($preco, 2);
    }
    // App/Models/Produto.php

    public function precoAtual()
    {
        // preço base
        $preco = $this->preco_venda;

        // aplica desconto fixo
        if(!empty($this->desconto) && $this->desconto > 0){
            $preco -= $this->desconto;
        }

        // aplica desconto percentual
        if(!empty($this->desconto_percentual) && $this->desconto_percentual > 0){
            $preco = $preco * (1 - $this->desconto_percentual / 100);
        }

        return round($preco, 2);
    }

}
