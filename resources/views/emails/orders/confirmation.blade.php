<x-mail::message>
# Order Confirmed!

Thank you for your order. Here are the details:

**Order Number:** {{ $order->order_number }}
**Date:** {{ $order->created_at->format('d M Y') }}

<x-mail::table>
| Item | Qty | Price |
|:-----|:---:|------:|
@foreach ($order->items as $item)
| {{ $item->product_name }} | {{ $item->quantity }} | {{ number_format($item->subtotal, 2) }} {{ $currency }} |
@endforeach
</x-mail::table>

@if ($order->discount_amount > 0)
**Discount:** -{{ number_format($order->discount_amount, 2) }} {{ $currency }}
@endif

**Total: {{ number_format($order->total, 2) }} {{ $currency }}**

---

**Billing Address**
{{ $order->billing_address['name'] ?? '' }}
{{ $order->billing_address['line1'] ?? '' }}
{{ $order->billing_address['city'] ?? '' }}, {{ $order->billing_address['zip'] ?? '' }}

@if ($order->payment_method === 'bank_transfer')
<x-mail::panel>
**Awaiting Bank Transfer**

Please transfer **{{ number_format($order->total, 2) }} {{ $currency }}** using your order number **{{ $order->order_number }}** as the payment reference. Your order will be confirmed once payment is received.
</x-mail::panel>
@endif

<x-mail::button :url="url('/account/orders/' . $order->id)">
View Order
</x-mail::button>

Thanks,<br>
{{ $siteName }}
</x-mail::message>
