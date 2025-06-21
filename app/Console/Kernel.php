<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        Commands\CheckBacklinksCommand::class,
        Commands\TestBacklinkCommand::class,
        Commands\DebugLinksCommand::class,
        Commands\SendBacklinkReportsCommand::class,
    ];

    protected function schedule(Schedule $schedule): void
    {
        // Vérifier les backlinks toutes les heures
        $schedule->command('backlinks:check --limit=100')
                 ->hourly()
                 ->withoutOverlapping();

        // Envoyer les rapports quotidiens à 8h (seulement si problèmes)
        $schedule->command('backlinks:send-reports')
                 ->dailyAt('08:00');

        // Envoyer les rapports hebdomadaires le lundi à 9h (même sans problèmes)
        $schedule->command('backlinks:send-reports')
                 ->weeklyOn(1, '09:00');
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
