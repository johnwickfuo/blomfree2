<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderDeliveredCustomer extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $statusUrl;

    public function __construct(public Order $order)
    {
        $this->statusUrl = route('orders.show', ['order' => $order->reference]);
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Delivered — '.$this->order->reference);
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.order-delivered-customer');
    }
}
