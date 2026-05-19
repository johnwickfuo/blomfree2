<?php

namespace App\Mail;

use App\Models\Affiliate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AffiliateWelcomeEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $dashboardUrl;

    public string $termsUrl;

    public function __construct(public Affiliate $affiliate)
    {
        $this->dashboardUrl = route('affiliate.dashboard');
        $this->termsUrl = route('affiliate.terms');
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Welcome to the BLOMFREE Affiliate Program');
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.affiliate-welcome');
    }
}
