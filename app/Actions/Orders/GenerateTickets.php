<?php

namespace App\Actions\Orders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Ticket;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class GenerateTickets
{
    use AsAction;

    public function handle(Order $order): void
    {
        $order->loadMissing('items');

        foreach ($order->items as $item) {
            // Only generate tickets for booking-type items (Activities, Accommodations, Vehicles)
            if (! $item->orderable_type) {
                continue;
            }

            // Skip if ticket already exists for this item
            if (Ticket::where('order_item_id', $item->id)->exists()) {
                continue;
            }

            $this->createTicket($order, $item);
        }
    }

    private function createTicket(Order $order, OrderItem $item): Ticket
    {
        $code = strtoupper(Str::random(4).'-'.Str::random(4).'-'.Str::random(4));

        $qrContent = route('account.orders.ticket', ['order' => $order->id, 'ticket' => $code]);

        $qrCode = (string) QrCode::format('svg')
            ->size(200)
            ->margin(1)
            ->generate($qrContent);

        return Ticket::create([
            'order_id' => $order->id,
            'order_item_id' => $item->id,
            'ticket_code' => $code,
            'qr_code' => $qrCode,
            'attendee_name' => $order->billing_address['name'] ?? null,
        ]);
    }
}
