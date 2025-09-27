<?php

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

    /**
     * Define a relação: um Usuário tem muitas Avaliações Posturais.
     */
    public function avaliacoesPosturais()
    {
        return $this->hasMany(AvaliacaoPostural::class, 'usuario_id');
    }

    /**
     * Define a relação: um Usuário tem muitas Inscrições.
     */
    public function inscricoes()
    {
        return $this->hasMany(Inscricao::class);
    }

    /**
     * Define a relação: um Usuário tem muitas Aulas.
     */
    public function aulas()
    {
        return $this->hasMany(Aula::class);
    }

    /**
     * Informa ao Laravel que a nossa coluna de senha se chama 'senha'.
     */
    public function getAuthPasswordName()
    {
        return 'senha';
    }
}