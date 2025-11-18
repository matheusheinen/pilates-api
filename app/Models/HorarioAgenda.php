<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        return $this->belongsToMany(Inscricao::class, 'horario_inscricao', 'horario_agenda_id', 'inscricao_id')
                    ->withTimestamps();
    }
}
