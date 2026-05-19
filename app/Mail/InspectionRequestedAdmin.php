<?php

namespace App\Mail;

use App\Models\Inspection;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InspectionRequestedAdmin extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $adminUrl;

    public function __construct(public Inspection $inspection)
    {
        $this->adminUrl = route('filament.admin.resources.inspections.edit', [
            'record' => $inspection->getKey(),
        ]);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Inspection Request — '.$this->inspection->reference,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.inspection-requested-admin',
        );
    }
}
