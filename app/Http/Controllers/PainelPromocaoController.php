<?php

namespace App\Http\Controllers;

use App\Models\Promocao;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PainelPromocaoController extends Controller
{
    public function index()
    {
        $hoje = Carbon::today();

        // Promoções ativas HOJE
        $promocoesAtivas = Promocao::where('status', 1)
            // ->where('promocao_inicio', '<=', $hoje)
            // ->where('promocao_fim', '>=', $hoje)
            ->with('produto')
            ->paginate(5, ['*'], 'ativas');

        // Promoções ENCERRADAS (últimos 30 dias)
        $promocoesEncerradas = Promocao::where('status', 0)
            // ->where('promocao_fim', '<', $hoje) 
            ->with('produto')
            ->orderBy('promocao_fim', 'desc')
            ->paginate(5, ['*'], 'encerradas');

        return view('painel_promocao.index', compact(
            'promocoesAtivas',
            'promocoesEncerradas'
        ));
    }
}

//  $hoje = Carbon::today();

//         // Promoções ativas HOJE
//         $promocoesAtivas = Promocao::where('promocao_inicio', '<=', $hoje)
//             ->where('promocao_fim', '>=', $hoje)
//             ->with('produto')
//             ->paginate(5, ['*'], 'ativas');

//         // Promoções ENCERRADAS (últimos 30 dias)
//         $promocoesEncerradas = Promocao::where('promocao_fim', '<', $hoje)
//             ->with('produto')
//             ->orderBy('promocao_fim', 'desc')
//             ->paginate(5, ['*'], 'encerradas');

//         return view('painel_promocao.index', compact(
//             'promocoesAtivas',
//             'promocoesEncerradas'
//         ));