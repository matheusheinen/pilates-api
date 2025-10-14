<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define a agenda de comandos da aplicação.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Agora, apenas um comando é agendado para ser executado a cada hora.
        // Ele vai gerar novas aulas E atualizar o status das aulas existentes.
        $schedule->command('app:atualizar-agenda')->hourly();
    }

    /**
     * Regista os comandos para a aplicação.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
