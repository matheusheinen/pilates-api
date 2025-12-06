<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens; // <-- adicione isto

class Usuario extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable; // <-- e use o trait HasApiTokens

    protected $table = 'usuarios';

    protected $fillable = [
        'nome',
        'email',
        'senha',
        'tipo',
        'cpf',
        'genero',
        'data_nascimento',
        'profissao',
        'celular',
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

    public function inscricoes()
    {
        return $this->hasMany(Inscricao::class);
    }

    public function aulas()
    {
        return $this->hasMany(Aula::class);
    }

    public function getAuthPasswordName()
    {
        return 'senha';
    }
}
