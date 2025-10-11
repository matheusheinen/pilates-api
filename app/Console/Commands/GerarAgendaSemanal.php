<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\HorarioAgenda; // ATUALIZADO: Usar o novo Model
use App\Models\Aula;
use Carbon\Carbon;

class GerarAgendaSemanal extends Command
{
    /**
     * A assinatura do comando do console.
     * {semana? : Opcional. A data para a qual gerar a agenda (formato YYYY-MM-DD). Gera para a semana seguinte se não for especificado.}
     */
    protected $signature = 'app:gerar-agenda-semanal {semana?}';

    /**
     * A descrição do comando do console.
     */
    protected $description = 'Gera as aulas de uma semana com base nos horários definidos na agenda.';

    /**
     * Executa a lógica do comando.
     */
    public function handle()
    {
        $this->info('Iniciando a geração da agenda semanal...');

        // 1. Determina para qual semana a agenda será gerada.
        // Se uma data foi passada, usa a semana dessa data.
        // Se não, gera para a próxima semana.
        $dataReferencia = $this->argument('semana')
            ? Carbon::parse($this->argument('semana'))
            : Carbon::now()->addWeek();

        $inicioDaSemana = $dataReferencia->copy()->startOfWeek(Carbon::MONDAY);
        $fimDaSemana = $dataReferencia->copy()->endOfWeek(Carbon::SUNDAY);

        $this->info("Gerando agenda para a semana de: " . $inicioDaSemana->format('d/m/Y') . " a " . $fimDaSemana->format('d/m/Y'));

        // 2. Busca todos os horários/slots definidos na tabela 'horarios_agenda'.
        $slotsDaAgenda = HorarioAgenda::with('inscricao')->get();

        if ($slotsDaAgenda->isEmpty()) {
            $this->warn('Nenhum horário padrão encontrado em horarios_agenda. Nenhuma aula foi gerada.');
            return 0;
        }

        $aulasCriadas = 0;

        // 3. Itera sobre cada dia da semana que estamos a gerar (de Segunda a Domingo).
        for ($dia = $inicioDaSemana->copy(); $dia->lte($fimDaSemana); $dia->addDay()) {

            // 4. Para cada dia, percorre todos os slots da agenda.
            foreach ($slotsDaAgenda as $slot) {
                // 5. Verifica se o dia da semana corresponde ao dia do slot.
                // Carbon usa 1 para Segunda (dayOfWeekIso), que corresponde ao nosso sistema.
                if ($dia->dayOfWeekIso == $slot->dia_semana) {

                    // 6. ANTES DE CRIAR, verifica se já não existe uma aula para este horário e dia.
                    // Isto impede a criação de aulas duplicadas se o comando for executado várias vezes.
                    Aula::firstOrCreate(
                        [
                            'data_hora' => $dia->copy()->setTimeFromTimeString($slot->horario_inicio),
                        ],
                        [
                            // Dados a serem preenchidos se a aula for criada pela primeira vez:
                            'inscricao_id' => $slot->inscricao_id, // Pode ser nulo, indicando um horário livre
                            'status' => $slot->inscricao_id ? 'agendada' : 'disponivel', // Se tem inscrição, está 'agendada', senão, 'disponivel'
                        ]
                    );
                    $aulasCriadas++;
                }
            }
        }

        $this->info("Processo concluído. {$aulasCriadas} aulas foram verificadas/criadas para a semana.");
        return 0;
    }
}
