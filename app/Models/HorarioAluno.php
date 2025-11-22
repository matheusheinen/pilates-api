<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HorarioAluno extends Model
{
    protected $table = 'horarios_aluno';
    protected $fillable = ['inscricao_id', 'horario_agenda_id', 'status'];

    public function inscricao() {
        return $this->belongsTo(Inscricao::class, 'inscricao_id');
    }

    public function agenda() {
        // Relação com o slot fixo da escola
        return $this->belongsTo(HorarioAgenda::class, 'horario_agenda_id');
    }
}
