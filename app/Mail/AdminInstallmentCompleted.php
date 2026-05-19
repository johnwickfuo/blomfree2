<?php

namespace App\Mail;

use App\Models\InstallmentPlan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminInstallmentCompleted extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $adminUrl;

    public function __construct(public InstallmentPlan $plan)
    {
        $this->adminUrl = url('/admin/installment-plans/'.$plan->getKey());
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Installment plan completed: '.$this->plan->reference);
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.admin-installment-completed');
    }
}
