<x-mail::message>
# You left something behind 🛒

Hi {{ $cart->user->name }},

You started a great order — your cart is still waiting for you. Here's what's inside:

<x-mail::table>
| Item | Qty | Price |
|:-----|:---:|------:|
@foreach ($cart->items as $item)
| **{{ $item->product->name ?? $item->cartable?->title ?? 'Item' }}** | {{ $item->quantity }} | {{ number_format($item->unit_price * $item->quantity, 2) }} {{ $currency }} |
@endforeach
</x-mail::table>

<table style="width:100%; border-top: 2px solid #4f46e5; margin-top: 8px;">
<tr>
  <td style="color:#1e1b4b; font-size:16px; font-weight:bold; padding: 12px 0 4px;">Cart Total</td>
  <td style="text-align:right; color:#4f46e5; font-size:18px; font-weight:bold; padding: 12px 0 4px;">{{ number_format($cart->total, 2) }} {{ $currency }}</td>
</tr>
</table>

Stock is limited — complete your order before items sell out.

<x-mail::button :url="url('/cart')">
Return to My Cart
</x-mail::button>

Thanks,<br>
{{ $siteName }}
</x-mail::message>
