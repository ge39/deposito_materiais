<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CepController extends Controller
{
    public function buscar(Request $request)
    {
        $cep = preg_replace('/[^0-9]/', '', $request->cep);

        if (strlen($cep) !== 8) {
            return response()->json(['erro' => 'CEP inválido'], 400);
        }

        $url = "https://viacep.com.br/ws/{$cep}/json/";

        $response = @file_get_contents($url);

        if (!$response) {
            return response()->json(['erro' => 'CEP não encontrado ou falha na conexão'], 404);
        }

        return response($response, 200)
            ->header('Content-Type', 'application/json');
    }
}
