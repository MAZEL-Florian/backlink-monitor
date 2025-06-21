<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Mail\BacklinkStatusReport;
use App\Services\BacklinkReportService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendBacklinkReportsCommand extends Command
{
    protected $signature = 'backlinks:send-reports {--user_id= : ID de l\'utilisateur spécifique}';
    protected $description = 'Envoyer les rapports de backlinks par email';

    public function handle(BacklinkReportService $reportService)
    {
        $userId = $this->option('user_id');
        
        if ($userId) {
            $users = User::where('id', $userId)->where('email_notifications', true)->get();
        } else {
            $users = User::where('email_notifications', true)->get();
        }

        if ($users->isEmpty()) {
            $this->info('Aucun utilisateur avec notifications activées trouvé.');
            return 0;
        }

        $this->info("Envoi des rapports à {$users->count()} utilisateur(s)...");

        foreach ($users as $user) {
            try {
                $reportData = $reportService->generateReportData($user);
                
                $hasIssues = $reportData['inactive_backlinks']->count() > 0 || 
                           $reportData['error_backlinks']->count() > 0;
                
                if ($hasIssues || $this->isWeeklyReport()) {
                    Mail::to($user->email)->send(new BacklinkStatusReport($user, $reportData));
                    
                    $this->line("✓ Rapport envoyé à {$user->email}");
                } else {
                    $this->line("- Pas de problème pour {$user->email}, rapport non envoyé");
                }
                
            } catch (\Exception $e) {
                $this->error("✗ Erreur pour {$user->email}: " . $e->getMessage());
            }
        }

        $this->info('Envoi des rapports terminé.');
        return 0;
    }

    private function isWeeklyReport(): bool
    {
        return now()->isMonday();
    }
}
