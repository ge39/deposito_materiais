<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BackupConfiguracao extends Model
{
    protected $table = 'backup_configuracoes';

    protected $fillable = [
        'ativo',
        'frequencia',
        'horario',
        'manter_ultimos',
        'backup_banco',
        'backup_arquivos',
        'compactar_zip',
        'destino',
    ];

    protected $casts = [
        'ativo' => 'boolean',
        'backup_banco' => 'boolean',
        'backup_arquivos' => 'boolean',
        'compactar_zip' => 'boolean',
    ];
}