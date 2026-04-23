<x-mail::message>
# You left something behind!

Hi {{ $cart->user->name }},

You have items waiting in your cart. Don't let them get away!

<x-mail::table>
| Item | Qty | Price |
|:-----|:---:|------:|
@foreach ($cart->items as $item)
| {{ $item->product->name }} | {{ $item->quantity }} | {{ number_format($item->unit_price * $item->quantity, 2) }} {{ $currency }} |
@endforeach
</x-mail::table>

**Total: {{ number_format($cart->total, 2) }} {{ $currency }}**

<x-mail::button :url="url('/cart')">
Complete My Order
</x-mail::button>

Thanks,<br>
{{ $siteName }}
</x-mail::message>
