<?php
namespace App\Http\Controllers\PDV;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Orcamento;

class OrcamentoPDVController extends Controller
{
    public function buscar($codigo)
    {
        $orcamento = Orcamento::where('codigo_orcamento', $codigo)->first();

        if (!$orcamento) {
            return response()->json(['message' => 'Orçamento não encontrado'], 404);
        }

        return response()->json($orcamento);
    }
}
  2ed
