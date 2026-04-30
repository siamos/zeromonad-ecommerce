<?php

namespace App\Mail;

use App\Models\ReturnRequest;
use App\Settings\GeneralSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReturnRequestReceived extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public readonly ReturnRequest $returnRequest) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            to: $this->returnRequest->user->email,
            subject: "Return Request Received — Order {$this->returnRequest->order->order_number}",
            tags: ['return-request-received'],
            metadata: ['return_request_id' => $this->returnRequest->id],
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.returns.received',
            with: [
                'returnRequest' => $this->returnRequest,
                'order' => $this->returnRequest->order,
                'siteName' => app(GeneralSettings::class)->site_name ?? config('app.name'),
            ],
        );
    }
}
