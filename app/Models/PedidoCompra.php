<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PedidoCompra extends Model
{
    use HasFactory;

    protected $table = 'pedido_compras';

    protected $fillable = [
        'user_id',
        'fornecedor_id',
        'data_pedido',
        'status',
        'total',
    ];

    protected $casts = [
        'data_pedido' => 'date',
        'total' => 'decimal:2',
    ];

    // ================================================================
    // RELACIONAMENTOS
    // ================================================================

    public function fornecedor()
    {
        return $this->belongsTo(Fornecedor::class);
    }

    public function lotes()
    {
        return $this->hasMany(Lote::class, 'pedido_compra_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function itens()
    {
        return $this->hasMany(PedidoItem::class, 'pedido_id');
    }


    // ===============================================================================
    // MÉTODO PRINCIPAL - RECEBIMENTO ACID COM CONTROLE DE CONCORRÊNCIA
    // ===============================================================================

    public function receberProdutos()
    {
        if ($this->status !== 'aprovado') {
            throw new \Exception("Recebimento só é permitido para pedidos 'aprovado'.");
        }

        DB::transaction(function () {

            // ===========================================================
            // BLOQUEAR O PEDIDO + ITENS PARA EVITAR CONCORRÊNCIA
            // ===========================================================
            $this->lockForUpdate();

            $itens = $this->itens()->lockForUpdate()->with('produto')->get();

            foreach ($itens as $item) {

                $produto = $item->produto->lockForUpdate()->first();

                if (!$produto) {
                    throw new \Exception("Produto associado ao item não encontrado.");
                }

                // ============================================================================
                // 1. Atualiza a quantidade total (CAMPO DO PRODUTO)
                // ============================================================================
                $produto->quantidade_estoque += $item->quantidade;
                $produto->save();


                // ============================================================================
                // 2. PROCESSAMENTO DO LOTE
                // ============================================================================

                // Garante número único e estável para o lote
                $numeroLote = $item->numero_lote
                    ?? ($produto->id . '-' . now()->format('YmdHis'));

                $lote = Lote::where('produto_id', $produto->id)
                    ->where('numero_lote', $numeroLote)
                    ->lockForUpdate()
                    ->first();

                if (!$lote) {
                    // Criar novo lote
                    $lote = new Lote([
                        'produto_id' => $produto->id,
                        'numero_lote' => $numeroLote,
                    ]);
                }

                // Soma quantidades com concorrência protegida
                $lote->quantidade = ($lote->quantidade ?? 0) + $item->quantidade;

                // Validade prioriza: item → produto → null
                $lote->validade = $item->validade
                    ?? $produto->validade_produto
                    ?? null;

                $lote->fornecedor_id = $this->fornecedor_id;
                $lote->data_compra = $this->data_pedido;

                $lote->save();
            }

            // ============================================================================
            // 3. Atualiza status do pedido
            // ============================================================================
            $this->status = 'recebido';
            $this->save();
        });
    }
}
