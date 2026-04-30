<?php

namespace App\Actions\Orders;

use App\Mail\ReturnRequestReceived;
use App\Models\Order;
use App\Models\ReturnRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

class RequestReturn
{
    use AsAction;

    public function handle(Order $order, string $reason, ?string $details = null): ReturnRequest
    {
        if (! in_array($order->status, ['delivered', 'shipped']) || $order->payment_status !== 'paid') {
            throw ValidationException::withMessages(['reason' => 'Returns can only be requested for paid delivered orders.']);
        }

        if ($order->created_at->diffInDays(now()) > 30) {
            throw ValidationException::withMessages(['reason' => 'The 30-day return window for this order has passed.']);
        }

        $existing = $order->returnRequests()->whereIn('status', ['requested', 'approved'])->first();

        if ($existing) {
            throw ValidationException::withMessages(['reason' => 'A return request for this order is already pending.']);
        }

        $returnRequest = $order->returnRequests()->create([
            'user_id' => $order->user_id,
            'reason' => $reason,
            'details' => $details,
            'status' => 'requested',
        ]);

        Mail::to($order->user->email)->queue(new ReturnRequestReceived($returnRequest));

        return $returnRequest;
    }

    public function asController(Request $request, Order $order): RedirectResponse
    {
        $request->validate([
            'reason' => 'required|string|max:255',
            'details' => 'nullable|string|max:1000',
        ]);

        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        $this->handle($order, $request->reason, $request->details);

        return back()->with('success', 'Return request submitted. We will review it and get back to you.');
    }
}
