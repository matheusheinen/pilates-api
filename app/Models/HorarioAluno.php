<?php

namespace App\Models;

// MUDANÇA 1: Importar Pivot em vez de Model (ou além dele)
use Illuminate\Database\Eloquent\Relations\Pivot;

// MUDANÇA 2: Estender Pivot
class HorarioAluno extends Pivot
{
    protected $table = 'horarios_aluno';

    // MUDANÇA 3: Indicar que essa tabela pivot tem um ID autoincremental
    // Por padrão, classes Pivot assumem que não existe chave primária 'id'.
    // Como sua migration criou um $table->id(), isso é OBRIGATÓRIO.
    public $incrementing = true;

    protected $fillable = [
        'inscricao_id',
        'horario_agenda_id',
        'status'
    ];

    // As relações podem ser mantidas se você precisar acessá-las a partir do pivô
    public function inscricao() {
        return $this->belongsTo(Inscricao::class, 'inscricao_id');
    }

    public function agenda() {
        return $this->belongsTo(HorarioAgenda::class, 'horario_agenda_id');
    }
}
