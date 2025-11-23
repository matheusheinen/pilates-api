<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Inscricao; // Certifique-se de importar
use Illuminate\Support\Facades\Log;

class AgendaAtualizar extends Command
{
    // O nome que será usado no terminal: php artisan agenda:atualizar
    // Opcional: Removendo o argumento {--dias}, já que a lógica está no modelo.
    protected $signature = 'agenda:atualizar';

    protected $description = 'Gera automaticamente as aulas futuras na agenda para todas as inscrições ativas (até o dia 10 do próximo mês).';

    public function handle()
    {
        // $dias foi removido. A lógica de data está agora em Inscricao::gerarAulasFuturas().

        $inscricoesAtivas = Inscricao::where('status', 'ativa')->get();

        // Mensagem de log ajustada para refletir a nova lógica.
        $this->info("Iniciando geração de aulas para {$inscricoesAtivas->count()} inscrições ativas, até o dia 10 do próximo mês...");
        Log::info('Rotina de geração de agenda iniciada.');

        foreach ($inscricoesAtivas as $inscricao) {
            try {
                // Chama a lógica de geração para cada inscrição ativa
                // O parâmetro $dias foi removido da chamada.
                $inscricao->gerarAulasFuturas();
                $this->line("• Aulas geradas para Aluno ID: {$inscricao->usuario_id}");
            } catch (\Exception $e) {
                $this->error("Falha ao gerar aulas para Inscrição ID {$inscricao->id}: " . $e->getMessage());
                Log::error("Falha na geração de agenda. Inscricao ID {$inscricao->id}: " . $e->getMessage());
            }
        }

        $this->info('Geração de agenda concluída.');
        Log::info('Rotina de geração de agenda concluída.');
    }
}
