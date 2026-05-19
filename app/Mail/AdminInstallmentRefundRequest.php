<?php

namespace App\Mail;

use App\Models\InstallmentRefund;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminInstallmentRefundRequest extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $adminUrl;

    public function __construct(public InstallmentRefund $refund)
    {
        $this->adminUrl = url('/admin/installment-refunds/'.$refund->getKey());
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Installment refund needs processing: '.$this->refund->reference);
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.admin-installment-refund-request');
    }
}
