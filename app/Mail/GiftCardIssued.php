<?php

namespace App\Mail;

use App\Models\GiftCard;
use App\Settings\GeneralSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class GiftCardIssued extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly GiftCard $giftCard,
        public readonly string $recipientEmail,
        public readonly ?string $recipientName = null,
        public readonly ?string $personalMessage = null,
    ) {}

    public function envelope(): Envelope
    {
        $siteName = app(GeneralSettings::class)->site_name ?? config('app.name');

        return new Envelope(
            to: $this->recipientEmail,
            subject: "You've received a gift card from {$siteName}!",
            tags: ['gift-card-issued'],
            metadata: ['gift_card_id' => $this->giftCard->id],
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.gift-cards.issued',
            with: [
                'giftCard' => $this->giftCard,
                'recipientName' => $this->recipientName,
                'personalMessage' => $this->personalMessage,
                'siteName' => app(GeneralSettings::class)->site_name ?? config('app.name'),
                'currency' => app(GeneralSettings::class)->currency ?? 'EUR',
            ],
        );
    }
}
