<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClienteCreditoResumo extends Model
{
    protected $table = 'vw_cliente_credito_resumo';

    public $timestamps = false;

    protected $primaryKey = 'cliente_id';

    public $incrementing = false;
}