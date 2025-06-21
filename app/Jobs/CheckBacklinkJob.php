<?php

namespace App\Jobs;

use App\Models\Backlink;
use App\Models\BacklinkCheck;
use App\Services\BacklinkCheckerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class CheckBacklinkJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Backlink $backlink,
        public bool $isAutomaticCheck = false,
        public string $checkType = 'automatic'
    ) {}

    public function handle(BacklinkCheckerService $checker): void
    {
        try {
            Log::info("Début du job de vérification", [
                'backlink_id' => $this->backlink->id,
                'check_type' => $this->checkType,
                'is_automatic' => $this->isAutomaticCheck
            ]);

            $wasActive = $this->backlink->is_active;
            $result = $checker->check($this->backlink);
            
            $check = BacklinkCheck::createFromBacklink($this->backlink, $result, $this->checkType);
            
            Log::info("Log de vérification créé", [
                'check_id' => $check->id,
                'backlink_id' => $this->backlink->id,
                'is_active' => $result['is_active'],
                'status_code' => $result['status_code']
            ]);

            $this->backlink->update([
                'status_code' => $result['status_code'],
                'is_active' => $result['is_active'],
                'is_dofollow' => $result['is_dofollow'],
                'anchor_text' => $result['anchor_text'] ?? $this->backlink->anchor_text,
                'target_url' => $result['target_url'] ?? $this->backlink->target_url,
                'last_checked_at' => now(),
            ]);

            if ($this->isAutomaticCheck) {
                $this->storeAutomaticCheckResult($wasActive, $result['is_active']);
            }

            Log::info("Vérification terminée avec succès", [
                'backlink_id' => $this->backlink->id,
                'check_id' => $check->id
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la vérification du backlink ' . $this->backlink->id . ': ' . $e->getMessage());
            
            $check = BacklinkCheck::createFromBacklink($this->backlink, [
                'status_code' => null,
                'is_active' => false,
                'is_dofollow' => $this->backlink->is_dofollow,
                'anchor_text' => $this->backlink->anchor_text,
                'response_time' => null,
                'error_message' => $e->getMessage(),
                'exact_match' => false,
                'target_url' => $this->backlink->target_url,
                'user_agent' => null,
                'redirects' => [],
                'content_length' => null,
                'content_type' => null,
                'raw_response' => null,
            ], $this->checkType);

            if ($this->isAutomaticCheck) {
                $this->storeAutomaticCheckResult($this->backlink->is_active, false, $e->getMessage());
            }
        }
    }

    private function storeAutomaticCheckResult(bool $wasActive, bool $isActive, ?string $error = null): void
    {
        $userId = $this->backlink->project->user_id;
        $cacheKey = "automatic_check_results_{$userId}";
        
        $results = Cache::get($cacheKey, [
            'user_id' => $userId,
            'check_time' => now(),
            'total_checked' => 0,
            'status_changes' => [],
            'errors' => [],
            'active_count' => 0,
            'inactive_count' => 0,
        ]);

        $results['total_checked']++;

        if ($isActive) {
            $results['active_count']++;
        } else {
            $results['inactive_count']++;
        }

        if ($wasActive !== $isActive) {
            $results['status_changes'][] = [
                'backlink_id' => $this->backlink->id,
                'project_name' => $this->backlink->project->name,
                'source_url' => $this->backlink->source_url,
                'source_domain' => $this->backlink->source_domain,
                'target_url' => $this->backlink->target_url,
                'anchor_text' => $this->backlink->anchor_text,
                'was_active' => $wasActive,
                'is_active' => $isActive,
                'status_code' => $this->backlink->status_code,
            ];
        }

        if ($error) {
            $results['errors'][] = [
                'backlink_id' => $this->backlink->id,
                'project_name' => $this->backlink->project->name,
                'source_url' => $this->backlink->source_url,
                'source_domain' => $this->backlink->source_domain,
                'error_message' => $error,
            ];
        }

        Cache::put($cacheKey, $results, now()->addHours(2));
    }
}
