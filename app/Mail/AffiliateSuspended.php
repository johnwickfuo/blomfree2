<?php

namespace App\Mail;

use App\Models\Affiliate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AffiliateSuspended extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $dashboardUrl;

    public function __construct(
        public Affiliate $affiliate,
        public ?string $reason,
        public bool $isReactivation = false,
    ) {
        $this->dashboardUrl = route('affiliate.dashboard');
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->isReactivation
            ? 'Your affiliate account has been reactivated'
            : 'Your affiliate account has been suspended');
    }

    public function content(): Content
    {
        return new Content(markdown: $this->isReactivation
            ? 'emails.affiliate-reactivated'
            : 'emails.affiliate-suspended');
    }
}
