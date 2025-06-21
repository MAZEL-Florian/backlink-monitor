<?php

namespace App\Console\Commands;

use App\Models\Backlink;
use App\Jobs\CheckBacklinkJob;
use Illuminate\Console\Command;

class CheckBacklinksCommand extends Command
{
    protected $signature = 'backlinks:check {--project_id= : ID du projet à vérifier} {--limit=50 : Nombre maximum de backlinks à vérifier}';
    protected $description = 'Vérifier les backlinks automatiquement';

    public function handle()
    {
        $projectId = $this->option('project_id');
        $limit = $this->option('limit');

        $query = Backlink::whereHas('project', function($q) {
            $q->where('is_active', true);
        });

        if ($projectId) {
            $query->where('project_id', $projectId);
        }

        // Prioriser les backlinks qui n'ont pas été vérifiés récemment
        $backlinks = $query->orderByRaw('COALESCE(last_checked_at, "1970-01-01") ASC')
                          ->limit($limit)
                          ->get();

        $this->info("Lancement de la vérification de {$backlinks->count()} backlinks...");

        foreach ($backlinks as $backlink) {
            CheckBacklinkJob::dispatch($backlink);
            $this->line("Backlink {$backlink->id} ajouté à la queue");
        }

        $this->info('Vérifications lancées avec succès !');
    }
}
