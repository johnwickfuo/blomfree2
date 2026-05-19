<?php

namespace App\Mail;

use App\Models\Affiliate;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AffiliateCommissionReversed extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Affiliate $affiliate,
        public Order $order,
        public float $totalReversed,
        public bool $isAdminCopy = false,
    ) {}

    public function envelope(): Envelope
    {
        $subject = $this->isAdminCopy
            ? 'Affiliate balance went negative after reversal — '.$this->affiliate->code
            : 'Commission reversed — '.$this->order->reference;

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(markdown: $this->isAdminCopy
            ? 'emails.affiliate-commission-reversed-admin'
            : 'emails.affiliate-commission-reversed');
    }
}
