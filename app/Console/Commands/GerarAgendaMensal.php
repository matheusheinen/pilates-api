<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\HorarioFixo;
use App\Models\Aula;
use Carbon\Carbon;

class GerarAgendaMensal extends Command
{
    /**
     * A assinatura do comando do console.
     * {mes? : Opcional. O mês para o qual gerar a agenda (formato YYYY-MM).}
     */
    protected $signature = 'app:gerar-agenda-mensal {mes?}';

    /**
     * A descrição do comando do console.
     */
    protected $description = 'Gera as aulas do mês com base nos horários fixos das inscrições ativas.';

    /**
     * Executa a lógica do comando.
     */
    public function handle()
    {
        $this->info('Iniciando a geração da agenda...');

        // 1. Determina para qual mês a agenda será gerada.
        // Se um mês foi passado como argumento (ex: 2025-10), usa esse.
        // Se não, gera para o próximo mês.
        $mesParaGerar = $this->argument('mes') 
            ? Carbon::createFromFormat('Y-m', $this->argument('mes'))
            : Carbon::now()->addMonth();

        $primeiroDiaDoMes = $mesParaGerar->copy()->startOfMonth();
        $ultimoDiaDoMes = $mesParaGerar->copy()->endOfMonth();

        $this->info("Gerando agenda para: " . $primeiroDiaDoMes->format('F Y'));

        // 2. Busca todos os horários fixos de inscrições que estão ATIVAS.
        $horariosFixos = HorarioFixo::whereHas('inscricao', function ($query) {
            $query->where('ativo', true);
        })->with('inscricao')->get();

        if ($horariosFixos->isEmpty()) {
            $this->warn('Nenhum horário fixo ativo encontrado. Nenhuma aula foi gerada.');
            return 0;
        }

        $aulasCriadas = 0;

        // 3. Percorre cada dia do mês que estamos a gerar.
        for ($dia = $primeiroDiaDoMes->copy(); $dia->lte($ultimoDiaDoMes); $dia->addDay()) {
            
            // 4. Para cada dia, percorre todos os horários fixos.
            foreach ($horariosFixos as $horarioFixo) {
                // 5. Verifica se o dia da semana do calendário corresponde ao dia do horário fixo.
                // Carbon usa 1 para Segunda e 7 para Domingo, o que corresponde ao nosso sistema.
                if ($dia->dayOfWeekIso == $horarioFixo->dia_da_semana) {
                    
                    // 6. ANTES DE CRIAR, verifica se já não existe uma aula para este aluno neste dia/hora.
                    // Isto impede a criação de aulas duplicadas.
                    Aula::firstOrCreate(
                        [
                            'usuario_id' => $horarioFixo->inscricao->usuario_id,
                            'data_aula' => $dia->toDateString(),
                            'horario' => $horarioFixo->horario,
                        ],
                        [
                            // Estes dados só são preenchidos se a aula for criada (firstOrCreate)
                            'horario_fim' => $horarioFixo->horario_fim,
                            'status' => 'agendada',
                            'tipo' => 'normal',
                        ]
                    );
                    $aulasCriadas++;
                }
            }
        }

        $this->info("Processo concluído. {$aulasCriadas} aulas foram verificadas/criadas.");
        return 0;
    }
}