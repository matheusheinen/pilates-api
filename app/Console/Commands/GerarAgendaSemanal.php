<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\HorarioAgenda;
use App\Models\Aula;
use Carbon\Carbon;

class GerarAgendaSemanal extends Command
{
    /**
     * A assinatura do comando, agora renomeado.
     */
    protected $signature = 'app:gerar-agenda-semanal {semana?}';

    /**
     * A descrição do comando.
     */
    protected $description = 'Gera as aulas de uma semana com base nos horários definidos na agenda.';

    /**
     * Executa a lógica do comando.
     */
    public function handle()
    {
        $this->info('Iniciando a geração da agenda semanal...');

        $dataReferencia = $this->argument('semana')
            ? Carbon::parse($this->argument('semana'))
            : Carbon::now()->addWeek();

        $inicioDaSemana = $dataReferencia->copy()->startOfWeek(Carbon::MONDAY);
        $fimDaSemana = $dataReferencia->copy()->endOfWeek(Carbon::SUNDAY);

        $this->info("Gerando agenda para a semana de: " . $inicioDaSemana->format('d/m/Y') . " a " . $fimDaSemana->format('d/m/Y'));

        $slotsDaAgenda = HorarioAgenda::all();

        if ($slotsDaAgenda->isEmpty()) {
            $this->warn('Nenhum horário padrão encontrado em horarios_agenda. Nenhuma aula foi gerada.');
            return 0;
        }

        $aulasCriadasCount = 0;

        for ($dia = $inicioDaSemana->copy(); $dia->lte($fimDaSemana); $dia->addDay()) {

            foreach ($slotsDaAgenda as $slot) {

                if ($dia->dayOfWeekIso == $slot->dia_semana) {

                    // Cria ou encontra a aula, prevenindo duplicados
                    $aula = Aula::firstOrCreate(
                        [
                            // Chave única para encontrar a aula
                            'data_hora_inicio' => $dia->copy()->setTimeFromTimeString($slot->horario_inicio),
                        ],
                        [
                            // Dados a serem preenchidos se a aula for criada
                            'duracao_minutos' => $slot->duracao_minutos, // Copia a duração
                            'inscricao_id' => $slot->inscricao_id,
                            'status' => $slot->inscricao_id ? 'agendada' : 'disponivel',
                        ]
                    );

                    // Se a aula foi criada agora, incrementa o contador
                    if ($aula->wasRecentlyCreated) {
                        $aulasCriadasCount++;
                    }
                }
            }
        }

        $this->info("Processo concluído. {$aulasCriadasCount} novas aulas foram criadas para a semana.");
        return 0;
    }
}
