<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\HorarioAgenda;
use App\Models\Aula;
use Carbon\Carbon;

class AtualizarAgenda extends Command
{
    /**
     * A assinatura do comando.
     */
    protected $signature = 'app:atualizar-agenda {--weeks=1}';

    /**
     * A descrição do comando.
     */
    protected $description = 'Gera novas aulas e atualiza o status das aulas existentes (confirmação/cancelamento).';

    /**
     * Executa a lógica do comando.
     */
    public function handle()
    {
        $this->info('Iniciando atualização da agenda...');

        // --- PARTE 1: Geração de novas aulas ---
        $this->line('--> Gerando novas aulas...');
        $semanasParaGerar = (int) $this->option('weeks');
        $dataReferencia = Carbon::now();
        $slotsDaAgenda = HorarioAgenda::where('status', 'ativo')->get();
        $totalAulasCriadas = 0;

        for ($i = 0; $i < $semanasParaGerar; $i++) {
            $inicioDaSemana = $dataReferencia->copy()->addWeeks($i)->startOfWeek(Carbon::MONDAY);
            $fimDaSemana = $dataReferencia->copy()->addWeeks($i)->endOfWeek(Carbon::SUNDAY);

            for ($dia = $inicioDaSemana->copy(); $dia->lte($fimDaSemana); $dia->addDay()) {
                foreach ($slotsDaAgenda as $slot) {
                    if ($dia->dayOfWeekIso == $slot->dia_semana) {
                        $aula = Aula::firstOrCreate(
                            ['data_hora_inicio' => $dia->copy()->setTimeFromTimeString($slot->horario_inicio)],
                            [
                                'duracao_minutos' => $slot->duracao_minutos,
                                'inscricao_id' => $slot->inscricao_id,
                                'status' => $slot->inscricao_id ? 'agendada' : 'disponivel',
                            ]
                        );
                        if ($aula->wasRecentlyCreated) {
                            $totalAulasCriadas++;
                        }
                    }
                }
            }
        }
        $this->info("{$totalAulasCriadas} novas aulas foram criadas.");

        // --- PARTE 2: Atualização de Status ---
        $this->line('--> Verificando status de aulas próximas...');
        $limiteDeTempo = now()->addHours(24);

        // Cancela aulas disponíveis
        $aulasCanceladas = Aula::where('status', 'disponivel')
                             ->where('data_hora_inicio', '>', now())
                             ->where('data_hora_inicio', '<', $limiteDeTempo)
                             ->update(['status' => 'cancelada']);
        if ($aulasCanceladas > 0) {
            $this->info("{$aulasCanceladas} aulas disponíveis foram canceladas.");
        }

        // Confirma aulas agendadas
        $aulasConfirmadas = Aula::where('status', 'agendada')
                              ->where('data_hora_inicio', '>', now())
                              ->where('data_hora_inicio', '<', $limiteDeTempo)
                              ->update(['status' => 'confirmada']);
        if ($aulasConfirmadas > 0) {
            $this->info("{$aulasConfirmadas} aulas com alunos foram confirmadas.");
        }

        $this->info('Atualização da agenda concluída.');
        return 0;
    }
}
