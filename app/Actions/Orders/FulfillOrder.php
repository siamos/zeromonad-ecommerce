<?php

namespace App\Actions\Orders;

use App\Models\Order;
use Lorisleiva\Actions\Concerns\AsAction;

class FulfillOrder
{
    use AsAction;

    public function handle(Order $order): Order
    {
        $order->update([
            'status'         => 'delivered',
            'payment_status' => 'paid',
        ]);

        return $order->fresh();
    }
}
