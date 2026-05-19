<?php

namespace App\Mail;

use App\Models\InstallmentPayment;
use App\Models\InstallmentPlan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InstallmentPaymentReceived extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $dashboardUrl;

    public function __construct(public InstallmentPlan $plan, public InstallmentPayment $payment)
    {
        $this->dashboardUrl = route('account.installments.show', ['plan' => $plan->reference]);
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Payment received — '.$this->plan->reference);
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.installment-payment-received');
    }
}
