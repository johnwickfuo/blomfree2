<?php

namespace App\Mail;

use App\Models\Inspection;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InspectionApprovedCustomer extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $statusUrl;

    public function __construct(public Inspection $inspection)
    {
        $this->statusUrl = route('inspections.show', ['inspection' => $inspection->reference]);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your inspection is approved — '.$this->inspection->reference,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.inspection-approved-customer',
        );
    }
}
