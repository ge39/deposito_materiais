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
        'estoque_minimo',
        'preco_venda',
        'validade_produto',
        'em_promocao',
        'peso',
        'largura',
        'altura',
        'profundidade',
        'localizacao_estoque',
        'imagem',
        'ativo',
        'editando_por', 
        'editando_em',
    ];
   
    protected $casts = [
        'preco_venda' => 'decimal:2',
        'promocao_inicio' => 'date',
        'validade_produto'=> 'date',
        'promocao_fim' => 'date',
        'em_promocao' => 'boolean',
    ];

    // -------------------------------
    // RELACIONAMENTOS
    // -------------------------------

    public function promocao()
    {
        return $this->hasOne(Promocao::class, 'produto_id')
            ->where('status', 1)
            ->whereDate('promocao_inicio', '<=', now())
            ->whereDate('promocao_fim', '>=', now());
    }

    public function lotes()
    {
        return $this->hasMany(Lote::class);
    }

    public function unidadeMedida()
    {
        return $this->belongsTo(
            UnidadeMedida::class,
            'unidade_medida_id', // FK na tabela produtos
            'id'                 // PK em unidades_medida
        );
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
    // ESTOQUE (total disponível)
    // -------------------------------
    public function getEstoqueAttribute()
    {
        return $this->lotes->sum('quantidade_disponivel');
    }

    // -------------------------------
    // MUTATORS
    // -------------------------------
    public function setValidadeProdutoAttribute($value)
    {
        $this->attributes['validade_produto'] = empty($value)
            ? null
            : Carbon::parse($value)->startOfDay();
    }

    // -------------------------------
    // NOME FORMATADO
    // -------------------------------
    public function getNomeFormatadoAttribute()
    {
        return Str::title($this->nome);
    }

    // -------------------------------
    // PREÇO MÉDIO DE COMPRA
    // -------------------------------
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

        return $totalQuantidade > 0
            ? round($totalValor / $totalQuantidade, 2)
            : 0;
    }

    // -------------------------------
    // PREÇO ATUAL COM PROMOÇÃO
    // -------------------------------
    public function getPrecoAtualAttribute()
    {
        $preco = $this->preco_base ?? $this->preco_venda;
        $hoje = Carbon::today();

        if (
            $this->em_promocao &&
            $this->promocao_inicio &&
            $this->promocao_fim &&
            $hoje->between(
                Carbon::parse($this->promocao_inicio),
                Carbon::parse($this->promocao_fim)
            )
        ) {
            if ($this->preco_promocional) {
                $preco = $this->preco_promocional;
            } elseif ($this->desconto_percentual) {
                $preco = $preco * (1 - $this->desconto_percentual / 100);
            }
        }

        return round($preco, 2);
    }

    // -------------------------------
    // PREÇO COM DESCONTO FIXO/%
    // -------------------------------
    public function precoAtual()
    {
        $preco = $this->preco_venda;

        if (!empty($this->desconto) && $this->desconto > 0) {
            $preco -= $this->desconto;
        }

        if (!empty($this->desconto_percentual) && $this->desconto_percentual > 0) {
            $preco = $preco * (1 - $this->desconto_percentual / 100);
        }

        return round($preco, 2);
    }
}
