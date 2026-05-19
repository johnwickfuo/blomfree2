<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class LowStockDigestAdmin extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Collection $variants,
        public Collection $animals,
    ) {
    }

    public function envelope(): Envelope
    {
        $total = $this->variants->count() + $this->animals->count();

        return new Envelope(
            subject: "Low stock digest — {$total} item(s) under threshold",
        );
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.low-stock-digest-admin');
    }
}
