<?php

namespace App\Mail;

use App\Models\InstallmentPlan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InstallmentCompleted extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public InstallmentPlan $plan) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Plan fully paid! — '.$this->plan->reference);
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.installment-completed');
    }
}
