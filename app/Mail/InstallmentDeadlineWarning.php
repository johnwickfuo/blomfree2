<?php

namespace App\Mail;

use App\Models\InstallmentPlan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InstallmentDeadlineWarning extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $dashboardUrl;

    public function __construct(public InstallmentPlan $plan, public int $daysLeft)
    {
        $this->dashboardUrl = route('account.installments.show', ['plan' => $plan->reference]);
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: "Only {$this->daysLeft} days left — ".$this->plan->reference);
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.installment-deadline-warning');
    }
}
