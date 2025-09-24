<?php
// Em app/Models/Usuario.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'usuarios';

    /**
     * A lista de atributos que podem ser preenchidos em massa.
     * Agora inclui TODOS os campos.
     */
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

    /**
     * Atributos que devem ser escondidos para segurança.
     */
    protected $hidden = [
        'senha',
        'remember_token',
    ];

    /**
     * Atributos que devem ser convertidos para outros tipos.
     */
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
