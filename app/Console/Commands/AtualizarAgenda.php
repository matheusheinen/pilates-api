<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\HorarioAgenda;
use App\Models\Aula;
use Carbon\Carbon;

class AtualizarAgenda extends Command
{
    /**
     * O nome e assinatura do comando no terminal.
     */
    protected $signature = 'app:atualizar-agenda {--weeks=4}';

    /**
     * A descrição do comando.
     */
    protected $description = 'Gera aulas futuras para os alunos com matrícula ativa.';

    /**
     * Executa a lógica do comando.
     */
    public function handle()
    {
        $this->info('⏳ Iniciando geração de aulas...');

        $semanasParaGerar = (int) $this->option('weeks');
        $hoje = Carbon::now();

        // 1. Busca horários ativos que tenham alunos inscritos
        // Carregamos a relação 'inscricoes' filtrando apenas as ativas
        $slotsAgenda = HorarioAgenda::where('status', 'ativo')
            ->with(['inscricoes' => function($query) {
                $query->where('status', 'ativa');
            }])
            ->get();

        $totalAulasCriadas = 0;

        // 2. Loop pelas próximas semanas
        for ($i = 0; $i < $semanasParaGerar; $i++) {
            $inicioSemana = $hoje->copy()->addWeeks($i)->startOfWeek(Carbon::MONDAY);
            $fimSemana    = $hoje->copy()->addWeeks($i)->endOfWeek(Carbon::SATURDAY);

            // Loop dia a dia
            for ($data = $inicioSemana->copy(); $data->lte($fimSemana); $data->addDay()) {

                $diaSemanaLoop = $data->dayOfWeekIso; // 1 (Seg) a 7 (Dom)

                foreach ($slotsAgenda as $slot) {

                    // Verifica se o slot é para este dia da semana
                    if ($slot->dia_semana == $diaSemanaLoop) {

                        // Define a data e hora exata da aula
                        $dataHoraAula = $data->copy()->setTimeFromTimeString($slot->horario_inicio);

                        // Ignora aulas no passado
                        if ($dataHoraAula->lt(now())) {
                            continue;
                        }

                        // 3. GERAÇÃO: Cria uma aula para CADA aluno inscrito neste horário
                        foreach ($slot->inscricoes as $inscricao) {

                            // Verifica se a aula é posterior ao início do plano do aluno
                            if ($dataHoraAula->lt(Carbon::parse($inscricao->data_inicio))) {
                                continue;
                            }

                            // firstOrCreate evita duplicatas se rodar o comando várias vezes
                            $aula = Aula::firstOrCreate(
                                [
                                    'inscricao_id'     => $inscricao->id,
                                    'data_hora_inicio' => $dataHoraAula,
                                ],
                                [
                                    'duracao_minutos' => $slot->duracao_minutos,
                                    'status'          => 'agendada',
                                    'observacoes'     => null
                                ]
                            );

                            if ($aula->wasRecentlyCreated) {
                                $totalAulasCriadas++;
                            }
                        }
                    }
                }
            }
        }

        $this->info("✅ Agenda atualizada! {$totalAulasCriadas} novas aulas foram agendadas.");

        // Atualiza status de aulas passadas/próximas
        $this->atualizarStatusAutomatico();

        return 0;
    }

    private function atualizarStatusAutomatico()
    {
        $this->line('--> Verificando confirmação de aulas próximas...');

        // Exemplo: Confirma aulas agendadas para as próximas 24h
        $limiteTempo = now()->addHours(24);

        $afetadas = Aula::where('status', 'agendada')
            ->where('data_hora_inicio', '<', $limiteTempo)
            ->where('data_hora_inicio', '>', now())
            ->update(['status' => 'confirmada']);

        if ($afetadas > 0) {
            $this->info("ℹ️  {$afetadas} aulas próximas foram confirmadas automaticamente.");
        }
    }
}
