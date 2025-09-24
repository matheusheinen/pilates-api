<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $table = 'usuarios';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nome',
        'email',
        'senha',
        'role',
    ];

    public function aluno()
    {    
        return $this->hasOne(Aluno::class, 'usuario_id');
    }
    
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>

    protected $hidden = [
        'senha',
        'remember_token',
    ];
    */

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     * 
    protected function casts(): array
    {
        return [
            'email_verificado' => 'datetime',
            'senha' => 'hashed',
        ];
    }
    */
}
