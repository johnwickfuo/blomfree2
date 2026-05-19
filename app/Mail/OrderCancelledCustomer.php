<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderCancelledCustomer extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $landsUrl;

    public function __construct(public Order $order, public ?string $reason = null)
    {
        $this->landsUrl = route('lands.index');
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Order cancelled — '.$this->order->reference);
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.order-cancelled-customer');
    }
}
