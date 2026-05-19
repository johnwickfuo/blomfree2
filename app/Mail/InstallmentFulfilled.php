<?php

namespace App\Mail;

use App\Models\InstallmentPlan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InstallmentFulfilled extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public InstallmentPlan $plan) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->plan->subsidiary === 'lands'
            ? 'Your land documents are ready — '.$this->plan->reference
            : 'Your item is on the way — '.$this->plan->reference);
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.installment-fulfilled');
    }
}
