<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BacklinkStatusReport extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public array $reportData
    ) {}

    public function envelope(): Envelope
    {
        $totalIssues = $this->reportData['inactive_backlinks']->count() + 
                      $this->reportData['error_backlinks']->count();
        
        $subject = $totalIssues > 0 
            ? "⚠️ Rapport Backlinks - {$totalIssues} problème(s) détecté(s)"
            : "✅ Rapport Backlinks - Tout fonctionne correctement";

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.backlink-status-report',
        );
    }
}
