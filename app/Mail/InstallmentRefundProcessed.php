<?php

namespace App\Mail;

use App\Models\InstallmentRefund;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InstallmentRefundProcessed extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public InstallmentRefund $refund) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Your refund has been processed — '.$this->refund->reference);
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.installment-refund-processed');
    }
}
