<?php

namespace App\Mail;

use App\Models\Product;
use App\Settings\GeneralSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class StockAlertNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public readonly Product $product, public readonly string $recipient) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            to: $this->recipient,
            subject: "Back in stock: {$this->product->name}",
            tags: ['stock-alert'],
            metadata: ['product_id' => $this->product->id],
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.products.stock-alert',
            with: [
                'product' => $this->product,
                'siteName' => app(GeneralSettings::class)->site_name ?? config('app.name'),
            ],
        );
    }
}
