<x-mail::message>
# Order Cancelled

Hi {{ $order->billing_address['name'] ?? $order->user?->name ?? 'there' }},

Your order **{{ $order->order_number }}** has been cancelled as requested.

---

<x-mail::table>
| Item | Qty | Total |
|:-----|:---:|------:|
@foreach ($order->items as $item)
| **{{ $item->product_name }}** | {{ $item->quantity }} | {{ number_format($item->subtotal, 2) }} {{ $currency }} |
@endforeach
</x-mail::table>

**Order Total:** {{ number_format($order->total, 2) }} {{ $currency }}

---

@if ($order->payment_status === 'paid')
<x-mail::panel>
**Refund Information**

A refund of **{{ number_format($order->total, 2) }} {{ $currency }}** will be processed to your original payment method. Please allow 5–10 business days for it to appear on your statement.
</x-mail::panel>
@endif

If you did not request this cancellation or have any questions, please reply to this email immediately.

Thanks,<br>
{{ $siteName }}
</x-mail::message>
