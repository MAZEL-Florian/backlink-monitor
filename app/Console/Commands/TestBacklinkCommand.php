<?php

namespace App\Console\Commands;

use App\Models\Backlink;
use App\Models\BacklinkCheck;
use App\Services\BacklinkCheckerService;
use Illuminate\Console\Command;

class TestBacklinkCommand extends Command
{
    protected $signature = 'backlink:test {backlink_id : ID du backlink à tester}';
    protected $description = 'Tester la vérification d\'un backlink spécifique';

    public function handle(BacklinkCheckerService $checker): int
    {
        $backlinkId = $this->argument('backlink_id');
        $backlink = Backlink::find($backlinkId);

        if (!$backlink) {
            $this->error("Backlink avec l'ID {$backlinkId} introuvable.");
            return 1;
        }

        $this->info("🔍 Test du backlink ID: {$backlink->id}");
        $this->info("📍 URL Source: {$backlink->source_url}");
        $this->info("🎯 Projet: {$backlink->project->name} ({$backlink->project->domain})");
        $this->line('');

        $recentChecks = $backlink->checks()->latest('checked_at')->limit(5)->get();
        if ($recentChecks->count() > 0) {
            $this->info("📊 Dernières vérifications:");
            foreach ($recentChecks as $check) {
                $status = $check->is_active ? '✅ ACTIF' : '❌ INACTIF';
                $type = strtoupper($check->check_type);
                $this->line("  • {$check->checked_at->format('Y-m-d H:i')} - {$status} - {$type} - HTTP {$check->status_code}");
            }
            $this->line('');
        }

        $this->info("🚀 Lancement de la vérification...");
        
        try {
            $result = $checker->check($backlink);
            
            $check = BacklinkCheck::createFromBacklink($backlink, $result, 'test');
            
            $backlink->update([
                'status_code' => $result['status_code'],
                'is_active' => $result['is_active'],
                'is_dofollow' => $result['is_dofollow'],
                'anchor_text' => $result['anchor_text'] ?? $backlink->anchor_text,
                'target_url' => $result['target_url'] ?? $backlink->target_url,
                'last_checked_at' => now(),
            ]);

            $this->line('');
            $this->info("✅ Vérification terminée !");
            $this->info("📝 Log créé avec l'ID: {$check->id}");
            $this->line('');

            $this->table(['Propriété', 'Valeur'], [
                ['Statut', $result['is_active'] ? '✅ ACTIF' : '❌ INACTIF'],
                ['Code HTTP', $result['status_code'] ?? 'N/A'],
                ['Type de lien', $result['is_dofollow'] ? 'DoFollow' : 'NoFollow'],
                ['Texte d\'ancrage', $result['anchor_text'] ?? 'N/A'],
                ['URL cible', $result['target_url'] ?? 'N/A'],
                ['Temps de réponse', $result['response_time'] ? $result['response_time'] . 'ms' : 'N/A'],
                ['Correspondance exacte', $result['exact_match'] ? 'Oui' : 'Non'],
                ['Taille du contenu', $result['content_length'] ? number_format($result['content_length']) . ' octets' : 'N/A'],
                ['Type de contenu', $result['content_type'] ?? 'N/A'],
                ['Redirections', count($result['redirects'] ?? [])],
            ]);

            if (!empty($result['error_message'])) {
                $this->error("❌ Erreur: {$result['error_message']}");
            }

            return 0;

        } catch (\Exception $e) {
            $this->error("❌ Erreur lors de la vérification: {$e->getMessage()}");
            
            BacklinkCheck::createFromBacklink($backlink, [
                'status_code' => null,
                'is_active' => false,
                'is_dofollow' => $backlink->is_dofollow,
                'anchor_text' => $backlink->anchor_text,
                'response_time' => null,
                'error_message' => $e->getMessage(),
                'exact_match' => false,
                'target_url' => $backlink->target_url,
                'user_agent' => null,
                'redirects' => [],
                'content_length' => null,
                'content_type' => null,
                'raw_response' => null,
            ], 'test');

            return 1;
        }
    }
}
