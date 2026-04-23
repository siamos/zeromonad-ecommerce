<?php

namespace App\Mail;

use App\Models\Order;
use App\Settings\GeneralSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderStatusChanged extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public readonly Order $order, public readonly string $previousStatus) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            to: $this->order->billing_address['email'] ?? $this->order->user?->email,
            subject: "Order {$this->order->order_number} — Status Update",
            tags: ['order-status-changed'],
            metadata: ['order_id' => $this->order->id],
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.orders.status-changed',
            with: [
                'order' => $this->order,
                'previousStatus' => $this->previousStatus,
                'siteName' => app(GeneralSettings::class)->site_name ?? config('app.name'),
                'currency' => app(GeneralSettings::class)->currency ?? 'EUR',
            ],
        );
    }
}
