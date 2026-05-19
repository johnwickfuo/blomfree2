<?php

namespace App\Mail;

use App\Models\AffiliateWithdrawal;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AffiliateWithdrawalRequested extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $adminUrl;

    public function __construct(public AffiliateWithdrawal $withdrawal)
    {
        $this->adminUrl = url('/admin/affiliate-withdrawals/'.$withdrawal->getKey());
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'New withdrawal request: '.$this->withdrawal->reference);
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.affiliate-withdrawal-requested');
    }
}
