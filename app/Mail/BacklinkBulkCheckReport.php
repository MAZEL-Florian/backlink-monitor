<?php


namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BacklinkBulkCheckReport extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public array $results,
        public string $checkType = 'bulk'
    ) {}

    public function envelope(): Envelope
    {
        $totalChecked = count($this->results);
        $changedCount = collect($this->results)->where('status_changed', true)->count();
        
        $subject = "📊 Rapport de vérification - {$totalChecked} backlinks";
        if ($changedCount > 0) {
            $subject .= " ({$changedCount} changements)";
        }
        
        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.backlink-bulk-check-report');
    }
}
