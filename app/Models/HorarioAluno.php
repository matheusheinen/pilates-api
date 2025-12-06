<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class HorarioAluno extends Pivot
{
    protected $table = 'horarios_aluno';

    public $incrementing = true;

    protected $fillable = [
        'inscricao_id',
        'horario_agenda_id',
        'status'
    ];

    public function inscricao() {
        return $this->belongsTo(Inscricao::class, 'inscricao_id');
    }

    public function agenda() {
        return $this->belongsTo(HorarioAgenda::class, 'horario_agenda_id');
    }
}
