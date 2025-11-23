<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HorarioAgenda extends Model
{
    use HasFactory;

    protected $table = 'horarios_agenda';

    protected $fillable = [
        'dia_semana',
        'horario_inicio',
        'duracao_minutos',
        'vagas_totais', // <--- CAMPO NOVO OBRIGATÓRIO
        'status',
    ];

    // RELACIONAMENTO NOVO (Muitos para Muitos)
    public function inscricoes()
    {
        return $this->belongsToMany(Inscricao::class, 'horarios_aluno', 'horario_agenda_id', 'inscricao_id')
                    ->withPivot('status')
                    ->withTimestamps();
    }

    public function horariosAluno(): HasMany
    {
        // Usa a Foreign Key 'horario_agenda_id' que criamos na tabela horarios_aluno
        return $this->hasMany(HorarioAluno::class, 'horario_agenda_id');
    }
}
