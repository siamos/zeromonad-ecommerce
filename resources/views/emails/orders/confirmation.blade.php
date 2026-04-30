<x-mail::message>
# Order Confirmed ✓

Hi {{ $order->billing_address['name'] ?? $order->user?->name ?? 'there' }},

Thank you for your order! We've received it and are getting it ready. You'll receive another update when it ships.

---

**Order** {{ $order->order_number }} &nbsp;·&nbsp; {{ $order->created_at->format('d M Y, H:i') }}

<x-mail::table>
| Item | Qty | Unit Price | Total |
|:-----|:---:|:----------:|------:|
@foreach ($order->items as $item)
| **{{ $item->product_name }}**@if($item->product_sku) — {{ $item->product_sku }}@endif | {{ $item->quantity }} | {{ number_format($item->unit_price, 2) }} {{ $currency }} | {{ number_format($item->subtotal, 2) }} {{ $currency }} |
@endforeach
</x-mail::table>

<table style="width:100%; border-top: 1px solid #e4e4e7; padding-top: 8px;">
<tr>
  <td style="color:#52525b; font-size:14px; padding: 4px 0;">Subtotal</td>
  <td style="text-align:right; font-size:14px; padding: 4px 0;">{{ number_format($order->subtotal, 2) }} {{ $currency }}</td>
</tr>
@if ($order->discount_amount > 0)
<tr>
  <td style="color:#16a34a; font-size:14px; padding: 4px 0;">Discount</td>
  <td style="text-align:right; color:#16a34a; font-size:14px; padding: 4px 0;">−{{ number_format($order->discount_amount, 2) }} {{ $currency }}</td>
</tr>
@endif
@if ($order->tax_amount > 0)
<tr>
  <td style="color:#52525b; font-size:14px; padding: 4px 0;">Tax</td>
  <td style="text-align:right; font-size:14px; padding: 4px 0;">{{ number_format($order->tax_amount, 2) }} {{ $currency }}</td>
</tr>
@endif
<tr>
  <td style="color:#1e1b4b; font-size:16px; font-weight:bold; padding: 12px 0 4px; border-top: 2px solid #4f46e5;">Total</td>
  <td style="text-align:right; color:#4f46e5; font-size:18px; font-weight:bold; padding: 12px 0 4px; border-top: 2px solid #4f46e5;">{{ number_format($order->total, 2) }} {{ $currency }}</td>
</tr>
</table>

---

**Payment Method:** {{ ucwords(str_replace('_', ' ', $order->payment_method)) }}

@if ($order->payment_method === 'bank_transfer')
<x-mail::panel>
**Bank Transfer Instructions**

Please transfer **{{ number_format($order->total, 2) }} {{ $currency }}** to our account and use your order number **{{ $order->order_number }}** as the payment reference. Your order will be confirmed once payment clears (usually 1–2 business days).
</x-mail::panel>
@endif

---

**Billing Address**

{{ $order->billing_address['name'] ?? '' }}
{{ $order->billing_address['line1'] ?? '' }}@if(!empty($order->billing_address['line2']))
{{ $order->billing_address['line2'] }}@endif
{{ $order->billing_address['city'] ?? '' }}@if(!empty($order->billing_address['zip'])), {{ $order->billing_address['zip'] }}@endif

@if(!empty($order->billing_address['phone']))
**Phone:** {{ $order->billing_address['phone'] }}
@endif

@if(!empty($order->shipping_address['line1']) && ($order->shipping_address['line1'] ?? '') !== ($order->billing_address['line1'] ?? ''))

**Shipping Address**

{{ $order->shipping_address['name'] ?? '' }}
{{ $order->shipping_address['line1'] ?? '' }}
{{ $order->shipping_address['city'] ?? '' }}@if(!empty($order->shipping_address['zip'])), {{ $order->shipping_address['zip'] }}@endif
@endif

<x-mail::button :url="url('/account/orders/' . $order->id)">
View Order Details
</x-mail::button>

If you have any questions reply to this email — we're always happy to help.

Thanks,<br>
{{ $siteName }}
</x-mail::message>
