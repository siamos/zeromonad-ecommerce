<?php

namespace App\Actions\Orders;

use App\Mail\OrderCancelled;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Lorisleiva\Actions\Concerns\AsAction;

class CancelOrder
{
    use AsAction;

    public const WINDOW_HOURS = 24;

    public function handle(Order $order): void
    {
        abort_if(
            ! in_array($order->status, ['pending', 'processing']),
            403,
            'This order can no longer be cancelled.'
        );

        abort_if(
            $order->created_at->lt(now()->subHours(self::WINDOW_HOURS)),
            403,
            'The cancellation window has passed.'
        );

        $order->update(['status' => 'cancelled']);

        $email = $order->billing_address['email'] ?? $order->user?->email;

        if ($email) {
            Mail::to($email)->queue(new OrderCancelled($order));
        }
    }

    public function asController(Order $order): RedirectResponse
    {
        abort_unless(
            $order->user_id === auth()->id(),
            403
        );

        $this->handle($order);

        return redirect()->route('account.orders.show', $order)
            ->with('success', 'Your order has been cancelled.');
    }
}
