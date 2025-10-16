<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PosVenda extends Model
{
    use HasFactory;

    protected $fillable = [
        'venda_id',
        'descricao',
        'status',
    ];

    public function venda()
    {
        return $this->belongsTo(Venda::class);
    }
}
