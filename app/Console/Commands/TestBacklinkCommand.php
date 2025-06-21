<?php

namespace App\Console\Commands;

use App\Models\Backlink;
use App\Services\BacklinkCheckerService;
use Illuminate\Console\Command;

class TestBacklinkCommand extends Command
{
    protected $signature = 'backlink:test {id : ID du backlink à tester}';
    protected $description = 'Tester la vérification d\'un backlink spécifique';

    public function handle(BacklinkCheckerService $checker)
    {
        $backlinkId = $this->argument('id');
        $backlink = Backlink::find($backlinkId);

        if (!$backlink) {
            $this->error("Backlink avec l'ID {$backlinkId} non trouvé.");
            return 1;
        }

        $this->info("=== TEST DU BACKLINK {$backlink->id} ===");
        $this->line("URL Source: {$backlink->source_url}");
        $this->line("URL Cible: {$backlink->target_url}");
        $this->line("Ancre enregistrée: " . ($backlink->anchor_text ?: 'N/A'));
        $this->line('');

        $this->info('🔍 Vérification en cours...');
        
        $result = $checker->check($backlink);

        $this->line('');
        $this->info('=== RÉSULTATS ===');
        
        // Status de l'URL source
        if ($result['status_code'] == 200) {
            $this->info("✅ URL Source accessible (HTTP {$result['status_code']})");
        } else {
            $this->error("❌ URL Source inaccessible (HTTP " . ($result['status_code'] ?? 'Erreur') . ")");
        }
        
        // Présence de l'URL cible
        if ($result['is_active']) {
            $this->info("✅ URL Cible trouvée et cliquable");
            $this->info("📝 Ancre détectée: " . ($result['anchor_text'] ?: 'Vide'));
            
            if ($result['is_dofollow']) {
                $this->info("🔗 Lien Dofollow");
            } else {
                $this->warn("🚫 Lien Nofollow");
            }
            
            if ($result['exact_match']) {
                $this->info("🎯 Correspondance exacte de l'URL");
            } else {
                $this->warn("⚠️  Correspondance approximative de l'URL");
            }
        } else {
            $this->error("❌ URL Cible non trouvée ou non cliquable");
        }
        
        $this->line("⏱️  Temps de réponse: {$result['response_time']}ms");
        
        if (isset($result['error_message'])) {
            $this->error("💥 Erreur: {$result['error_message']}");
        }

        return 0;
    }
}
