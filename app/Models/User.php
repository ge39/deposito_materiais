<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'funcionario_id',
        'name',
        'email',
        'password',
        'nivel',
        'funcionario_id',
        'ativo',
    ];

   
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Relacionamento com Funcionario
    public function funcionario()
    {
        return $this->belongsTo(Funcionario::class);
    }

    // Criptografa a senha automaticamente
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = bcrypt($value);
    }

    // Escopo para filtrar usuários ativos
    public function scopeAtivos($query)
    {
        return $query->where('ativo', 1);
    }
    protected $casts = [
    'ativo' => 'boolean', // converte 0/1 automaticamente
    ];
}
