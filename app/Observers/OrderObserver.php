<?php

namespace App\Observers;

use App\Mail\OrderStatusChanged;
use App\Models\Order;
use Illuminate\Support\Facades\Mail;

class OrderObserver
{
    public function updating(Order $order): void
    {
        if ($order->isDirty('status')) {
            $order->_previousStatus = $order->getOriginal('status');
        }
    }

    public function updated(Order $order): void
    {
        if (! isset($order->_previousStatus)) {
            return;
        }

        $recipient = $order->billing_address['email'] ?? $order->user?->email;

        if (! $recipient) {
            return;
        }

        Mail::send(new OrderStatusChanged($order, $order->_previousStatus));
    }
}
