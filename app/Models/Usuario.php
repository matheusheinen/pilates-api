<?php
// Em app/Models/Usuario.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Usuario extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'usuarios';

    protected $fillable = [
        'nome',
        'email',
        'senha',
        'tipo',
        'genero',
        'data_nascimento',
        'profissao',
        'celular',
        'altura',
        'peso',
        'queixa_principal',
        'lateralidade',
    ];


    protected $hidden = [
        'senha',
        'remember_token',
    ];

   
    protected function casts(): array
    {
        return [
            'email_verificado' => 'datetime',
            'senha' => 'hashed',
        ];
    }
    public function avaliacoesPosturais()
    {
        return $this->hasMany(AvaliacaoPostural::class, 'usuario_id');
    }
}
