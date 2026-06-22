<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Carbon\Carbon;

class Produto extends Model
{
    use HasFactory;

    /**
     * Os atributos que podem ser atribuídos em massa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nome',
        'custo_frete_unidade',
        'custo_imposto_entrada',
        'descricao', 
        'categoria_id', 
        'fornecedor_id', 
        'unidade_medida_id',
        'marca_id', 
        'estoque_minimo', 
        'preco_compra_atual', 
        'custo_frete_unidade',
        'custo_imposto_entrada',
        'custo_real_entrada',
        'percentual_imposto_saida',
        'percentual_comissao',
        'percentual_taxa_cartao',
        'markup_1',
        'markup_2',
        'markup_3',
        'quantidade_estoque',
        'preco_venda', 
        'desconto_max_1',
        'preco_venda_2',
        'desconto_max_2',
        'preco_venda_3',
        'desconto_max_3',
        'peso',
        'largura', 
        'altura', 
        'profundidade', 
        'localizacao_estoque', 
        'imagem',
        'ativo',
        'codigo_barras', 
        'sku', 
        'ncm',
        'cest',
        'origem',
        'cfop',
        'icms_csosn',
        'em_promocao', 
        'controla_validade',
        'validade_produto',
        'editando_por', 
        'editando_em'
    ];
    
    /**
     * As conversões de tipo dos atributos (substitui o antigo $dates).
     *
     * @var array<string, string>
     */
    protected $casts = [
        // Booleanos
        'ativo' => 'boolean',
        'em_promocao' => 'boolean',
        'controla_validade' => 'boolean',
        
        // Datas
        'validade_produto' => 'date',
        'editando_em' => 'datetime',
        'promocao_inicio' => 'date',
        'promocao_fim' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',

        // Inteiros
        'estoque_minimo' => 'integer',
        'quantidade_estoque' => 'integer',
        'origem' => 'integer',
        'editando_por' => 'integer',

        // Decimais
        'preco_compra_atual' => 'decimal:2',
        'custo_frete_unidade' => 'decimal:2',
        'custo_imposto_entrada' => 'decimal:2',
        'custo_real_entrada' => 'decimal:2',
        'percentual_imposto_saida' => 'decimal:2',
        'percentual_comissao' => 'decimal:2',
        'percentual_taxa_cartao' => 'decimal:2',
        'markup_1' => 'decimal:2',
        'markup_2' => 'decimal:2',
        'markup_3' => 'decimal:2',
        'preco_venda' => 'decimal:2',
        'desconto_max_1' => 'decimal:2',
        'preco_venda_2' => 'decimal:2',
        'desconto_max_2' => 'decimal:2',
        'preco_venda_3' => 'decimal:2',
        'desconto_max_3' => 'decimal:2',
        'peso' => 'decimal:2',
        'largura' => 'decimal:2',
        'altura' => 'decimal:2',
        'profundidade' => 'decimal:2',
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
        return $this->hasMany(Lote::class, 'produto_id');
    }

    public function itemVendas()
    {
        return $this->hasMany(ItemVenda::class, 'produto_id');
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

    public function editor()
    {
        return $this->belongsTo(User::class, 'editando_por');
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

    // =========================
    // ESTOQUE TOTAL (TODOS LOTES)
    // =========================
    public function getEstoqueTotalAttribute()
    {
        return $this->lotes()->sum('quantidade');
    }

    // =========================
    // RESERVADO (TODOS LOTES)
    // =========================
    public function getQuantidadeReservadaAttribute()
    {
        return $this->lotes()->sum('quantidade_reservada');
    }

    // =========================
    // DISPONÍVEL (ESTOQUE REAL)
    // =========================
    public function getDisponivelAttribute()
    {
        return $this->lotes()->sum(DB::raw('quantidade - quantidade_reservada'));
    }
    /**
     * Busca o preço de compra do lote mais recente
     * Acessível via $produto->preco_compra_lote
     */
    public function getPrecoCompraLoteAttribute()
    {
        // Busca o último lote criado para este produto
        $ultimoLote = $this->lotes()->latest()->first();

        return $ultimoLote ? $ultimoLote->preco_compra : 0;
    }

}
