<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AutomaticCheckReport extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public array $results
    ) {}

    public function envelope(): Envelope
    {
        $statusChanges = count($this->results['status_changes']);
        $errors = count($this->results['errors']);
        $totalIssues = $statusChanges + $errors;
        
        $subject = $totalIssues > 0 
            ? "⚠️ Vérification Automatique - {$totalIssues} problème(s) détecté(s)"
            : "✅ Vérification Automatique - Tout fonctionne correctement";

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.automatic-check-report',
        );
    }
}
