<x-mail::message>
# Your Order Status Has Been Updated

Hi {{ $order->billing_address['name'] ?? $order->user?->name ?? 'there' }},

Your order **{{ $order->order_number }}** status has changed.

| | |
|:--|:--|
| **New Status** | {{ ucfirst(str_replace('_', ' ', $order->status)) }} |
| **Previous Status** | {{ ucfirst(str_replace('_', ' ', $previousStatus)) }} |
| **Order Total** | {{ number_format($order->total, 2) }} {{ $currency }} |

@if ($order->status === 'shipped')
<x-mail::panel>
Your order is on its way! You will receive tracking information as soon as it becomes available.
</x-mail::panel>
@elseif ($order->status === 'delivered')
<x-mail::panel>
Your order has been delivered. We hope you enjoy your purchase!
</x-mail::panel>
@elseif ($order->status === 'cancelled')
<x-mail::panel>
Your order has been cancelled. If you have any questions, please don't hesitate to contact us.
</x-mail::panel>
@endif

<x-mail::button :url="url('/account/orders/' . $order->id)">
View Order Details
</x-mail::button>

Thanks,<br>
{{ $siteName }}
</x-mail::message>
