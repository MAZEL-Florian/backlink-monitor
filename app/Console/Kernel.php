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
        Commands\DebugBacklinkChecksCommand::class,
        Commands\SendBacklinkReportsCommand::class,
    ];

    protected function schedule(Schedule $schedule): void
    {
        // Vérifier les backlinks tous les jours à 8h
        $schedule->command('backlinks:check --limit=200 --send-report')
                 ->dailyAt('08:00')
                 ->withoutOverlapping();

        // Vérification supplémentaire toutes les 6 heures pour les backlinks critiques
        $schedule->command('backlinks:check --limit=50')
                 ->everySixHours()
                 ->withoutOverlapping();

        // Envoyer les rapports hebdomadaires le lundi à 9h
        $schedule->command('backlinks:send-reports')
                 ->weeklyOn(1, '09:00');
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
