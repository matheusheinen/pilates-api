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
    public function gerarAulasFuturas($dias = 30): void
    {
        // Busca os horários fixos ATIVOS deste aluno
        // Importante: Usar wherePivot para garantir que estamos pegando os vínculos ativos
        $horariosFixos = $this->horariosAluno()
                              ->wherePivot('status', 'ativo')
                              ->get();

        // Define a data limite: dia 10 do próximo mês
        $dataFimGeracao = Carbon::today()->addMonth()->day(10);

        $dataAtual = Carbon::parse($this->data_inicio)->isPast() ? Carbon::today() : Carbon::parse($this->data_inicio);
        $dataAtual->startOfDay();

        while ($dataAtual->lte($dataFimGeracao)) {
            $diaSemanaHoje = $dataAtual->dayOfWeekIso;

            foreach ($horariosFixos as $horarioAgenda) {
                // Verifica se o dia da semana bate
                if ($horarioAgenda->dia_semana == $diaSemanaHoje) {

                    // BUSCA AULA EXISTENTE (Query para encontrar o registro)
                    $aulaExistente = Aula::where('inscricao_id', $this->id)
                        ->whereDate('data_hora_inicio', $dataAtual->format('Y-m-d'))
                        ->where('horario_agenda_id', $horarioAgenda->id)
                        ->first(); // Usamos first() para pegar o objeto, não só checar se existe

                    if ($aulaExistente) {
                        // CENÁRIO 1: A aula existe, mas estava CANCELADA.
                        // Ação: Reativar a aula para 'agendada'.
                        if ($aulaExistente->status === 'cancelada') {
                            $aulaExistente->update([
                                'status' => 'agendada',
                                // Atualiza o ID do vínculo novo se necessário
                                'horario_aluno_id' => $horarioAgenda->pivot->id
                            ]);
                        }
                        // Se já estiver 'agendada' ou 'realizada', não fazemos nada.
                    } else {
                        // CENÁRIO 2: A aula não existe.
                        // Ação: Criar nova aula.
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
