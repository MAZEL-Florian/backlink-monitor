<?php

namespace App\Console\Commands;

use App\Models\Backlink;
use App\Models\BacklinkCheck;
use Illuminate\Console\Command;

class DebugBacklinkChecksCommand extends Command
{
    protected $signature = 'backlink:debug-checks {id : ID du backlink}';
    protected $description = 'Débugger les vérifications d\'un backlink';

    public function handle()
    {
        $backlinkId = $this->argument('id');
        $backlink = Backlink::find($backlinkId);

        if (!$backlink) {
            $this->error("Backlink avec l'ID {$backlinkId} non trouvé.");
            return 1;
        }

        $this->info("=== DEBUG BACKLINK {$backlink->id} ===");
        $this->line("URL Source: {$backlink->source_url}");
        $this->line("Statut actuel: " . ($backlink->is_active ? 'ACTIF' : 'INACTIF'));
        $this->line("Dernière vérification: " . ($backlink->last_checked_at ? $backlink->last_checked_at : 'Jamais'));
        $this->line('');

        $totalChecks = $backlink->checks()->count();
        $this->info("Total des vérifications: {$totalChecks}");

        if ($totalChecks > 0) {
            $recentChecks = $backlink->checks()->latest('checked_at')->take(10)->get();
            $this->info("=== 10 DERNIÈRES VÉRIFICATIONS ===");
            
            foreach ($recentChecks as $check) {
                $status = $check->is_active ? '✅ ACTIF' : '❌ INACTIF';
                $this->line("- {$check->checked_at}: {$status} (HTTP {$check->status_code})");
            }

            $this->line('');
            $this->info("=== VÉRIFICATIONS PAR JOUR (7 derniers jours) ===");
            
            $startDate = now()->subDays(7)->startOfDay();
            $endDate = now()->endOfDay();
            
            $checksByDay = $backlink->checks()
                ->where('checked_at', '>=', $startDate)
                ->where('checked_at', '<=', $endDate)
                ->orderBy('checked_at')
                ->get()
                ->groupBy(function($check) {
                    return $check->checked_at;
                });

            for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
                $dateKey = $date;
                $dayChecks = $checksByDay->get($dateKey, collect());
                
                if ($dayChecks->count() > 0) {
                    $lastCheck = $dayChecks->last();
                    $status = $lastCheck->is_active ? '✅ ACTIF' : '❌ INACTIF';
                    $this->line("- {$dateKey}: {$status} ({$dayChecks->count()} vérification(s))");
                } else {
                    $this->line("- {$dateKey}: ⚪ Aucune vérification");
                }
            }
        } else {
            $this->warn("Aucune vérification trouvée pour ce backlink.");
        }

        return 0;
    }
}
