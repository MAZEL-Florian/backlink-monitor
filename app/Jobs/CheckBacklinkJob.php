<?php

namespace App\Jobs;

use App\Models\Backlink;
use App\Models\BacklinkCheck;
use App\Services\BacklinkCheckerService;
use App\Mail\BacklinkStatusChanged;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CheckBacklinkJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Backlink $backlink
    ) {}

    public function handle(BacklinkCheckerService $checker): void
    {
        try {
            $wasActive = $this->backlink->is_active;
            $result = $checker->check($this->backlink);
            
            // Créer un enregistrement de vérification
            BacklinkCheck::create([
                'backlink_id' => $this->backlink->id,
                'status_code' => $result['status_code'],
                'is_active' => $result['is_active'],
                'is_dofollow' => $result['is_dofollow'],
                'anchor_text' => $result['anchor_text'],
                'response_time' => $result['response_time'],
                'checked_at' => now(),
                'error_message' => $result['error_message'] ?? null,
                'exact_match' => $result['exact_match'] ?? false,
            ]);

            // Mettre à jour le backlink
            $this->backlink->update([
                'status_code' => $result['status_code'],
                'is_active' => $result['is_active'],
                'is_dofollow' => $result['is_dofollow'],
                'anchor_text' => $result['anchor_text'] ?? $this->backlink->anchor_text,
                'last_checked_at' => now(),
            ]);

            // Vérifier si le statut a changé pour envoyer une notification
            if ($wasActive !== $result['is_active']) {
                $this->sendStatusChangeNotification($wasActive, $result['is_active']);
            }

        } catch (\Exception $e) {
            Log::error('Erreur lors de la vérification du backlink ' . $this->backlink->id . ': ' . $e->getMessage());
            
            BacklinkCheck::create([
                'backlink_id' => $this->backlink->id,
                'status_code' => null,
                'is_active' => false,
                'is_dofollow' => $this->backlink->is_dofollow,
                'anchor_text' => $this->backlink->anchor_text,
                'response_time' => null,
                'checked_at' => now(),
                'error_message' => $e->getMessage(),
                'exact_match' => false,
            ]);
        }
    }

    private function sendStatusChangeNotification(bool $wasActive, bool $isActive): void
    {
        $user = $this->backlink->project->user;
        
        if ($user->email_notifications) {
            try {
                Mail::to($user->email)->send(
                    new BacklinkStatusChanged($this->backlink, $wasActive, $isActive)
                );
                
                Log::info("Notification de changement de statut envoyée", [
                    'backlink_id' => $this->backlink->id,
                    'user_email' => $user->email,
                    'was_active' => $wasActive,
                    'is_active' => $isActive
                ]);
            } catch (\Exception $e) {
                Log::error("Erreur lors de l'envoi de notification", [
                    'backlink_id' => $this->backlink->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }
}
