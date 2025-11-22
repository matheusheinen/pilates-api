<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inscricao extends Model
{
    protected $table = 'inscricoes';

    protected $fillable = [
        'usuario_id',
        'plano_id',
        'data_inicio',
        'status' // Usamos status para ativo/inativo/trancado
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class);
    }

    public function plano(): BelongsTo
    {
        return $this->belongsTo(Plano::class);
    }

    // Relacionamento com os horários fixos reservados por esta inscrição
    public function horariosAluno(): HasMany {
        return $this->hasMany(HorarioAluno::class, 'inscricao_id');
    }

    /**
     * Gera as aulas reais na tabela 'aulas' para os próximos X dias.
     */
    public function gerarAulasFuturas($dias = 30): void
    {
        $horariosFixos = $this->horariosAluno()->with('agenda')->where('status', 'ativo')->get();
        $dataFimGeracao = Carbon::today()->addDays($dias);
        $dataAtual = Carbon::parse($this->data_inicio)->isPast() ? Carbon::today() : Carbon::parse($this->data_inicio);

        while ($dataAtual->lte($dataFimGeracao)) {
            $diaSemanaHoje = $dataAtual->dayOfWeekIso;

            foreach ($horariosFixos as $vinculo) {
                if ($vinculo->agenda->dia_semana == $diaSemanaHoje) {

                    // CRUCIAL: Checa a existência usando data_hora_inicio (nome da coluna no DB)
                    $jaExiste = Aula::where('inscricao_id', $this->id)
                        ->whereDate('data_hora_inicio', $dataAtual->format('Y-m-d'))
                        ->where('horario_agenda_id', $vinculo->horario_agenda_id)
                        ->exists();

                    if (!$jaExiste) {
                        $dataHoraInicio = Carbon::parse($dataAtual->format('Y-m-d') . ' ' . $vinculo->agenda->horario_inicio);

                        Aula::create([
                            'inscricao_id' => $this->id,
                            'horario_aluno_id' => $vinculo->id,
                            'horario_agenda_id' => $vinculo->horario_agenda_id,
                            'data_hora_inicio' => $dataHoraInicio,
                            'duracao_minutos' => $vinculo->agenda->duracao_minutos,
                            'status' => 'agendada',
                            'usuario_id' => $this->usuario_id
                        ]);
                    }
                }
            }
            $dataAtual->addDay();
        }
    }
}
