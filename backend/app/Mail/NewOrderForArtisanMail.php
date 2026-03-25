<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewOrderForArtisanMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Order $order,
        public string $artisanName,
        public array $products
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nuevo pedido #' . $this->order->id . ' - Asociacion de Artesanos',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.new-order-artisan',
            with: [
                'order' => $this->order->load('user'),
                'artisanName' => $this->artisanName,
                'products' => $this->products,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
