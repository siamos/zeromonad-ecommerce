<?php

namespace App\Notifications;

use App\Models\ReturnRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ReturnRequestUpdated extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly ReturnRequest $returnRequest,
    ) {}

    /** @return array<string> */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): \App\Mail\ReturnRequestUpdated
    {
        return new \App\Mail\ReturnRequestUpdated($this->returnRequest);
    }

    /** @return array<string, mixed> */
    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'return_request_updated',
            'title' => "Return request update — Order #{$this->returnRequest->order->order_number}",
            'body' => "Your return request status is now: {$this->returnRequest->status}.",
            'url' => route('account.orders.show', $this->returnRequest->order_id),
            'return_request_id' => $this->returnRequest->id,
            'status' => $this->returnRequest->status,
        ];
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
