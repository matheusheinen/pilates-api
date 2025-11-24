<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Inscricao extends Model
{
    protected $table = 'inscricoes';

    protected $fillable = [
        'usuario_id',
        'plano_id',
        'data_inicio',
        'status'
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class);
    }

    public function plano(): BelongsTo
    {
        return $this->belongsTo(Plano::class);
    }

    // DEFINIÇÃO CORRETA DO RELACIONAMENTO
    public function horariosAluno(): BelongsToMany
    {
        return $this->belongsToMany(HorarioAgenda::class, 'horarios_aluno', 'inscricao_id', 'horario_agenda_id')
                    ->withPivot('id', 'status') // Importante trazer o ID do pivot para salvar na aula
                    ->withTimestamps();
    }

    /**
     * Gera as aulas reais na tabela 'aulas'.
     */
    public function gerarAulasFuturas($dataLimite = null): void
    {
        $horariosFixos = $this->horariosAluno()
                              ->wherePivot('status', 'ativo')
                              ->get();

        // SEPARAÇÃO DA LÓGICA DE DATA LIMITE
        if ($dataLimite) {
            $dataFimGeracao = Carbon::parse($dataLimite);
        } else {
            // Regra padrão: dia 10 do próximo mês
            $dataFimGeracao = Carbon::today()->addMonth()->day(10);
        }

        // Garante que a geração comece de hoje ou da data de início (se for futura)
        $dataAtual = Carbon::parse($this->data_inicio)->isPast() ? Carbon::today() : Carbon::parse($this->data_inicio);
        $dataAtual->startOfDay();

        while ($dataAtual->lte($dataFimGeracao)) {
            $diaSemanaHoje = $dataAtual->dayOfWeekIso;

            foreach ($horariosFixos as $horarioAgenda) {
                if ($horarioAgenda->dia_semana == $diaSemanaHoje) {

                    $aulaExistente = Aula::where('inscricao_id', $this->id)
                        ->whereDate('data_hora_inicio', $dataAtual->format('Y-m-d'))
                        ->where('horario_agenda_id', $horarioAgenda->id)
                        ->first();

                    if ($aulaExistente) {
                        // Reativa aula cancelada se o pagamento renovou o período
                        if ($aulaExistente->status === 'cancelada') {
                            $aulaExistente->update([
                                'status' => 'agendada',
                                'horario_aluno_id' => $horarioAgenda->pivot->id
                            ]);
                        }
                    } else {
                        // Cria nova aula
                        $dataHoraInicio = Carbon::parse($dataAtual->format('Y-m-d') . ' ' . $horarioAgenda->horario_inicio);

                        Aula::create([
                            'inscricao_id' => $this->id,
                            'horario_aluno_id' => $horarioAgenda->pivot->id,
                            'horario_agenda_id' => $horarioAgenda->id,
                            'data_hora_inicio' => $dataHoraInicio,
                            'duracao_minutos' => $horarioAgenda->duracao_minutos,
                            'status' => 'agendada',
                            'usuario_id' => $this->usuario_id
                        ]);
                    }
                }
            }
            $dataAtual->addDay();
        }
    }

    public function cancelarAulasFuturas(): int
    {
         $agora = Carbon::now();
         return \App\Models\Aula::where('inscricao_id', $this->id)
            ->where('data_hora_inicio', '>=', $agora)
            ->where('status', 'agendada')
            ->update(['status' => 'cancelada']);
    }
}
