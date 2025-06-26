<?php

namespace App\Jobs;

use App\Mail\BacklinkBulkCheckReport;
use App\Mail\BacklinkStatusChanged;
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
use Illuminate\Support\Facades\Mail;

class CheckBacklinkJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

public function __construct(
    public Backlink $backlink,
    public bool $isAutomaticCheck = false,
    public string $checkType = 'automatic',
    public ?string $batchId = null 
) {}

 public function handle(BacklinkCheckerService $checker): void
{
    try {
        Log::info("Début du job de vérification", [
            'backlink_id' => $this->backlink->id,
            'check_type' => $this->checkType,
            'is_automatic' => $this->isAutomaticCheck,
            'batch_id' => $this->batchId
        ]);

        $wasActive = $this->backlink->is_active;
        $result = $checker->check($this->backlink);

        if (!$result['is_dofollow']) {
            $result['is_active'] = false;
        }

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

        if ($this->batchId) {
            $this->storeBatchResult($wasActive, $result['is_active'], $result);
        } elseif ($this->isAutomaticCheck) {
            $this->storeAutomaticCheckResult($wasActive, $result['is_active']);
        } else {
            if ($wasActive !== $result['is_active']) {
                try {
                    Mail::to($this->backlink->project->user->email)->send(
                        new BacklinkStatusChanged($this->backlink, $wasActive, $result['is_active'])
                    );
                } catch (\Exception $e) {
                    Log::error("Erreur lors de l'envoi de l'email de changement de statut", [
                        'backlink_id' => $this->backlink->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }

        Log::info("Vérification terminée avec succès", [
            'backlink_id' => $this->backlink->id,
            'check_id' => $check->id
        ]);
    } catch (\Exception $e) {
        Log::error('Erreur lors de la vérification du backlink ' . $this->backlink->id . ': ' . $e->getMessage());

        $errorResult = [
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
        ];

        if (!$errorResult['is_dofollow']) {
            $errorResult['is_active'] = false;
        }

        if ($this->batchId) {
            $this->storeBatchResult($this->backlink->is_active, false, $errorResult, $e->getMessage());
        } elseif ($this->isAutomaticCheck) {
            $this->storeAutomaticCheckResult($this->backlink->is_active, false, $e->getMessage());
        } else {
            if ($this->backlink->is_active) {
                try {
                    Mail::to($this->backlink->project->user->email)->send(
                        new BacklinkStatusChanged($this->backlink, true, false)
                    );
                } catch (\Exception $mailException) {
                    Log::error("Erreur lors de l'envoi de l'email d'erreur", [
                        'backlink_id' => $this->backlink->id,
                        'error' => $mailException->getMessage()
                    ]);
                }
            }
        }

        $check = BacklinkCheck::createFromBacklink($this->backlink, $errorResult, $this->checkType);
    }
}

private function storeBatchResult(bool $wasActive, bool $isActive, array $result, ?string $error = null): void
{
    $userId = $this->backlink->project->user_id;
    $cacheKey = "batch_check_results_{$this->batchId}";

    $results = Cache::get($cacheKey, [
        'user_id' => $userId,
        'batch_id' => $this->batchId,
        'check_time' => now(),
        'results' => [],
        'completed_count' => 0,
        'total_count' => 0,
    ]);

    $results['results'][] = [
        'backlink_id' => $this->backlink->id,
        'project_name' => $this->backlink->project->name,
        'source_url' => $this->backlink->source_url,
        'source_domain' => $this->backlink->source_domain,
        'target_url' => $this->backlink->target_url,
        'anchor_text' => $this->backlink->anchor_text,
        'was_active' => $wasActive,
        'is_active' => $isActive,
        'status_changed' => $wasActive !== $isActive,
        'status_code' => $result['status_code'] ?? null,
        'error_message' => $error,
        'response_time' => $result['response_time'] ?? null,
    ];

    $results['completed_count']++;

    Cache::put($cacheKey, $results, now()->addHours(2));

    if ($results['completed_count'] >= $results['total_count']) {
        $this->sendBatchReport($results);
    }
}

private function sendBatchReport(array $results): void
{
    try {
        $user = \App\Models\User::find($results['user_id']);
        if ($user) {
            Mail::to($user->email)->send(new BacklinkBulkCheckReport($results['results']));
            
            Log::info("Rapport de vérification groupé envoyé", [
                'batch_id' => $results['batch_id'],
                'user_id' => $results['user_id'],
                'total_checked' => count($results['results'])
            ]);
        }
        
        Cache::forget("batch_check_results_{$results['batch_id']}");
    } catch (\Exception $e) {
        Log::error("Erreur lors de l'envoi du rapport groupé", [
            'batch_id' => $results['batch_id'],
            'error' => $e->getMessage()
        ]);
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
