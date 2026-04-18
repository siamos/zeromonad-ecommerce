<?php

namespace App\Actions\Orders;

use App\Models\Order;
use Lorisleiva\Actions\Concerns\AsAction;

class CancelOrder
{
    use AsAction;

    public function handle(Order $order, string $reason = ''): Order
    {
        $order->update(['status' => 'cancelled']);

        // Restore product stock
        foreach ($order->items as $item) {
            if ($item->product_id) {
                $item->product?->increment('stock', $item->quantity);
            }
        }

        return $order->fresh();
    }
}
