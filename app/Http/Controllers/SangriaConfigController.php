<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SangriaConfig;
use App\Models\Empresa;

class SangriaConfigController extends Controller
{
    
    //  public function index()
    // {
    //     $config = SangriaConfig::where('empresa_id', auth()->user()->empresa_id)->first();
        
    //     return view('sangria-config.index', compact('config'));
    // }
    
    public function index()
    {
        $config = SangriaConfig::first(); // pega o primeiro registro
        return view('sangria-config.index', compact('config'));
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'valor_limite' => 'required|numeric|min:0'
        ]);

        $empresa = Empresa::ativa();

        if (!$empresa) {
            return back()->with('error', 'Nenhuma empresa ativa encontrada.');
        }

        SangriaConfig::updateOrCreate(
            ['empresa_id' => $empresa->id],
            [
                'empresa_id' => $empresa->id,
                'valor_limite' => $request->valor_limite
            ]
        );

        $valor = number_format($request->valor_limite, 2, ',', '.');

        // return back()->with('success', "O novo valor da sangria R$ {$valor} foi salvo!");
        return back()->with(
            'success',
            "💰 Sangria atualizada para R$ {$valor}"
        );
    }
}


