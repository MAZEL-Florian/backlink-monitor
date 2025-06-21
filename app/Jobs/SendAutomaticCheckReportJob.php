<?php

namespace App\Jobs;

use App\Models\User;
use App\Mail\AutomaticCheckReport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SendAutomaticCheckReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $userId
    ) {}

    public function handle(): void
    {
        $user = User::find($this->userId);
        
        if (!$user || !$user->email_notifications) {
            return;
        }

        $cacheKey = "automatic_check_results_{$this->userId}";
        $results = Cache::get($cacheKey);

        if (!$results || $results['total_checked'] === 0) {
            Log::info("Aucun résultat de vérification automatique trouvé pour l'utilisateur {$this->userId}");
            return;
        }

        try {
            $hasChanges = !empty($results['status_changes']);
            $hasErrors = !empty($results['errors']);
            
            if ($hasChanges || $hasErrors) {
                Mail::to($user->email)->send(new AutomaticCheckReport($user, $results));
                
                Log::info("Rapport de vérification automatique envoyé", [
                    'user_id' => $this->userId,
                    'user_email' => $user->email,
                    'total_checked' => $results['total_checked'],
                    'status_changes' => count($results['status_changes']),
                    'errors' => count($results['errors'])
                ]);
            } else {
                Log::info("Aucun problème détecté, rapport non envoyé", [
                    'user_id' => $this->userId,
                    'total_checked' => $results['total_checked'],
                    'active_count' => $results['active_count']
                ]);
            }

            Cache::forget($cacheKey);

        } catch (\Exception $e) {
            Log::error("Erreur lors de l'envoi du rapport automatique", [
                'user_id' => $this->userId,
                'error' => $e->getMessage()
            ]);
        }
    }
}
