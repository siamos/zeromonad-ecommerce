<?php

namespace App\Observers;

use App\Actions\Orders\GenerateTickets;
use App\Models\Order;
use App\Notifications\OrderStatusChanged;
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
        if ($order->wasChanged('payment_status') && $order->payment_status === 'paid') {
            GenerateTickets::run($order);
        }

        if (! isset($order->_previousStatus)) {
            return;
        }

        if ($order->user) {
            $order->user->notify(new OrderStatusChanged($order, $order->_previousStatus));

            return;
        }

        $recipient = $order->billing_address['email'] ?? null;

        if ($recipient) {
            Mail::send(new \App\Mail\OrderStatusChanged($order, $order->_previousStatus));
        }
    }
}
