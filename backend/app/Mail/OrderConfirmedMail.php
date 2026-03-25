<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderConfirmedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pedido #' . $this->order->id . ' confirmado - Asociacion de Artesanos',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.order-confirmed',
            with: [
                'order' => $this->order->load('items.product.artisan.user', 'user'),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
