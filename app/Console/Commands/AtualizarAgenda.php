<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\Aula;

class AgendaAtualizar extends Command
{
    // 1. Assinatura LIMPA (sem {--dias})
    protected $signature = 'agenda:atualizar';

    protected $description = 'Finaliza aulas passadas (muda status de agendada para realizada).';

    public function handle()
    {
        Log::info('Rotina de limpeza de agenda iniciada.');

        $agora = Carbon::now();

        // 2. Lógica ÚNICA: Atualizar o passado
        // Busca aulas 'agendada' que já passaram da hora e marca como 'realizada'
        $aulasAtualizadas = Aula::where('status', 'agendada')
            ->where('data_hora_inicio', '<', $agora)
            ->update(['status' => 'realizada']);

        if ($aulasAtualizadas > 0) {
            $this->info("✔ {$aulasAtualizadas} aulas passadas foram marcadas como 'realizada'.");
            Log::info("Agenda: {$aulasAtualizadas} aulas finalizadas automaticamente.");
        } else {
            $this->info("• Nenhuma aula pendente no passado.");
        }
    }
}
