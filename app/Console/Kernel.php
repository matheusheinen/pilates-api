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
        // CORREÇÃO: O nome deve ser igual ao $signature do comando
        $schedule->command('agenda:atualizar')->hourly();
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
