<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminCustomerMessage extends Mailable
{
    use Queueable, SerializesModels;

    public array $messageData;

    public function __construct(array $messageData)
    {
        $this->messageData = $messageData;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->messageData['subject'] ?? 'Message from PawPrints Team',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.customer_message',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
