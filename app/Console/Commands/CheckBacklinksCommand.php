<?php

namespace App\Console\Commands;

use App\Models\Backlink;
use App\Jobs\CheckBacklinkJob;
use App\Jobs\SendAutomaticCheckReportJob;
use Illuminate\Console\Command;

class CheckBacklinksCommand extends Command
{
    protected $signature = 'backlinks:check {--project_id= : ID du projet à vérifier} {--limit=50 : Nombre maximum de backlinks à vérifier} {--send-report : Envoyer un rapport après vérification}';
    protected $description = 'Vérifier les backlinks automatiquement';

    public function handle()
    {
        $projectId = $this->option('project_id');
        $limit = $this->option('limit');
        $sendReport = $this->option('send-report');

        $query = Backlink::whereHas('project', function($q) {
            $q->where('is_active', true);
        });

        if ($projectId) {
            $query->where('project_id', $projectId);
        }

        $backlinks = $query->orderByRaw('COALESCE(last_checked_at, "1970-01-01") ASC')
                          ->limit($limit)
                          ->get();

        $this->info("Lancement de la vérification de {$backlinks->count()} backlinks...");

        $backlinksByUser = $backlinks->groupBy(function($backlink) {
            return $backlink->project->user_id;
        });

        foreach ($backlinksByUser as $userId => $userBacklinks) {
            $this->line("Vérification de {$userBacklinks->count()} backlinks pour l'utilisateur {$userId}");
            
            foreach ($userBacklinks as $backlink) {
                CheckBacklinkJob::dispatch($backlink, true);
                $this->line("  - Backlink {$backlink->id} ajouté à la queue");
            }
        }

        if ($sendReport) {
            $this->info("Programmation de l'envoi des rapports dans 5 minutes...");
            
            foreach ($backlinksByUser->keys() as $userId) {
                SendAutomaticCheckReportJob::dispatch($userId)->delay(now()->addMinutes(5));
            }
        }

        $this->info('Vérifications lancées avec succès !');
    }
}
