<?php

namespace App\Mail;

use App\Filament\Resources\InquiryResource;
use App\Models\Inquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InquiryReceivedAdmin extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $adminUrl;

    public string $subjectLabel;

    public function __construct(public Inquiry $inquiry)
    {
        $this->adminUrl = InquiryResource::getUrl('edit', ['record' => $inquiry->getKey()]);
        $this->subjectLabel = $inquiry->subjectLabel();
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New inquiry: '.$this->subjectLabel.' — '.$this->inquiry->name,
        );
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.inquiry-received-admin');
    }
}
