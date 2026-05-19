<?php

namespace App\Mail;

use App\Models\Affiliate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminNewAffiliateSignup extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $adminUrl;

    public function __construct(public Affiliate $affiliate)
    {
        $this->adminUrl = url('/admin/affiliates/'.$affiliate->getKey());
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'New affiliate signup: '.$this->affiliate->code);
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.admin-new-affiliate-signup');
    }
}
