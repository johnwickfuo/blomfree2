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

class AffiliateCommissionEarned extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $dashboardUrl;

    public function __construct(
        public Affiliate $affiliate,
        public Order $order,
        public float $totalAccrued,
    ) {
        $this->dashboardUrl = route('affiliate.dashboard.commissions');
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'You earned ₦'.number_format($this->totalAccrued, 2).' in commissions');
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.affiliate-commission-earned');
    }
}
