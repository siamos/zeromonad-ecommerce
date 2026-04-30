<?php

namespace App\Mail;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WaitlistItemAvailable extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly Product $product) {}

    public function envelope(): Envelope
    {
        $name = is_array($this->product->name) ? ($this->product->name['en'] ?? '') : (string) $this->product->name;

        return new Envelope(subject: "{$name} is back in stock — you're on the waitlist!");
    }

    public function content(): Content
    {
        return new Content(view: 'emails.waitlist.available');
    }

    public function attachments(): array
    {
        return [];
    }
}
