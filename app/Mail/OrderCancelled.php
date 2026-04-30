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

class OrderCancelled extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public readonly Order $order) {}

    public function envelope(): Envelope
    {
        $siteName = app(GeneralSettings::class)->site_name ?? config('app.name');

        return new Envelope(
            to: $this->order->billing_address['email'] ?? $this->order->user?->email,
            subject: "Order Cancelled — {$this->order->order_number}",
            tags: ['order-cancelled'],
            metadata: ['order_id' => $this->order->id],
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.orders.cancelled',
            with: [
                'order' => $this->order->loadMissing('items'),
                'siteName' => app(GeneralSettings::class)->site_name ?? config('app.name'),
                'currency' => app(GeneralSettings::class)->currency ?? 'EUR',
            ],
        );
    }
}
