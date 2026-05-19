<?php

namespace App\Mail;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AnimalOrderReceivedAdmin extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $adminUrl;

    public function __construct(public Order $order)
    {
        $this->adminUrl = OrderResource::getUrl('edit', ['record' => $order->getKey()]);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'URGENT: Animal Order Received — '.$this->order->reference,
        );
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.animal-order-received-admin');
    }
}
