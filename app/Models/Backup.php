<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Backup extends Model
{
    protected $table = 'backups';

    protected $fillable = [
        'tipo',
        'nome_arquivo',
        'caminho',
        'tamanho_bytes',
        'hash_sha256',
        'status',
        'mensagem_erro',
        'usuario_id',
        'executado_em',
        'concluido_em',
    ];

    protected $casts = [
        'executado_em' => 'datetime',
        'concluido_em' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}