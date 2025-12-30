<?php
// app/Services/CaixaService.php
namespace App\Services;

use App\Models\Caixa;

class CaixaService
{
    public static function caixaAbertoParaUsuario($userId)
    {
        return Caixa::where('user_id', $userId)
            ->where('status', 'aberto')
            ->first();
    }
}
