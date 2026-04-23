<?php

namespace App\Mail;

use App\Models\Cart;
use App\Settings\GeneralSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AbandonedCartReminder extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public readonly Cart $cart) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            to: $this->cart->user->email,
            subject: 'You left something behind!',
            tags: ['abandoned-cart'],
            metadata: ['cart_id' => $this->cart->id],
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.carts.abandoned',
            with: [
                'cart' => $this->cart,
                'siteName' => app(GeneralSettings::class)->site_name ?? config('app.name'),
                'currency' => app(GeneralSettings::class)->currency ?? 'EUR',
            ],
        );
    }
}
