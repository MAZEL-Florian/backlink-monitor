<?php

namespace App\Mail;

use App\Models\Backlink;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BacklinkStatusChanged extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Backlink $backlink,
        public bool $wasActive,
        public bool $isActive
    ) {}

    public function envelope(): Envelope
    {
        $status = $this->isActive ? "✅ Réactivé" : "❌ Désactivé";
        
        return new Envelope(
            subject: "{$status} - Backlink {$this->backlink->source_domain}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.backlink-status-changed',
        );
    }
}
