<?php

// app/Models/Aluno.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Aluno extends Model
{
    use HasFactory;

    protected $table = 'alunos';

    protected $fillable = [
        'usuario_id',
        'genero',
        'data_nascimento',
        'profissao',
        'celular',
        'altura',
        'peso',
        'queixa_principal',
        'lateralidade',
    ];


    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }


    public function avaliacoesPosturais()
    {
        return $this->hasMany(AvaliacaoPostural::class, 'aluno_id');
    }
}
