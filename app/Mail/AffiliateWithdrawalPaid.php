<?php

namespace App\Mail;

use App\Models\AffiliateWithdrawal;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AffiliateWithdrawalPaid extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $dashboardUrl;

    public function __construct(public AffiliateWithdrawal $withdrawal)
    {
        $this->dashboardUrl = route('affiliate.dashboard.withdrawals');
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Withdrawal paid — '.$this->withdrawal->reference);
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.affiliate-withdrawal-paid');
    }
}
