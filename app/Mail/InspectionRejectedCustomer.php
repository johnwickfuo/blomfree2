<?php

namespace App\Mail;

use App\Models\Inspection;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InspectionRejectedCustomer extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $landsUrl;

    public function __construct(public Inspection $inspection)
    {
        $this->landsUrl = route('lands.index');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Update on your inspection request — '.$this->inspection->reference,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.inspection-rejected-customer',
        );
    }
}
