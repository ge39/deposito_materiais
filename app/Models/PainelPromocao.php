<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Promocao;
use Carbon\Carbon;

class PainelPromocaoController extends Controller
{
    
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
        'em_promocao',
    ];    
        
    public function index()
    {
       
        $hoje = Carbon::today();

        // Promoções ativas no momento
        $promocoesVigentes = Promocao::where('data_inicio', '<=', $hoje)
            ->where('data_fim', '>=', $hoje)
            ->with('produto')
            ->get();

        // Promoções encerradas no mês vigente
        $promocoesEncerradasMes = Promocao::whereMonth('data_fim', $hoje->month)
            ->whereYear('data_fim', $hoje->year)
            ->where('data_fim', '<', $hoje)
            ->with('produto')
            ->get();

        return view('painel_promocao.index', [
            'promocoesVigentes' => $promocoesVigentes,
            'promocoesEncerradasMes' => $promocoesEncerradasMes,
        ]);
    }
}
