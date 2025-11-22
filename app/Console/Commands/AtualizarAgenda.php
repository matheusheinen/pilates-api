<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Inscricao; // Certifique-se de importar
use Illuminate\Support\Facades\Log;

class AgendaAtualizar extends Command
{
    // O nome que será usado no terminal: php artisan agenda:atualizar
    protected $signature = 'agenda:atualizar {--dias=30 : Quantos dias à frente gerar as aulas}';

    protected $description = 'Gera automaticamente as aulas futuras na agenda para todas as inscrições ativas.';

    public function handle()
    {
        $dias = $this->option('dias');

        $inscricoesAtivas = Inscricao::where('status', 'ativa')->get();

        $this->info("Iniciando geração de aulas para os próximos {$dias} dias ({$inscricoesAtivas->count()} inscrições ativas)...");
        Log::info("Rotina de geração de agenda iniciada.");

        foreach ($inscricoesAtivas as $inscricao) {
            try {
                // Chama a lógica de geração para cada inscrição ativa
                $inscricao->gerarAulasFuturas($dias);
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
