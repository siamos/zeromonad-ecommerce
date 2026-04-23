<?php

namespace App\Jobs;

use App\Mail\AbandonedCartReminder;
use App\Models\Cart;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendAbandonedCartReminder implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Cart $cart) {}

    public function handle(): void
    {
        $email = $this->cart->user?->email;

        if (! $email || ! $this->cart->items()->exists()) {
            return;
        }

        Mail::send(new AbandonedCartReminder($this->cart->load('items.product.media')));
    }
}
