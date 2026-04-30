<x-mail::message>
# You're off the waitlist!

Hi there,

Good news — **{{ is_array($product->name) ? ($product->name['en'] ?? '') : $product->name }}** is back in stock and you're on the list!

<x-mail::panel>
**{{ is_array($product->name) ? ($product->name['en'] ?? '') : $product->name }}**

**Price: {{ number_format($product->price, 2) }} €**
</x-mail::panel>

Stock can go fast — be the first to grab it.

<x-mail::button :url="url('/shop/' . $product->slug)">
Shop Now
</x-mail::button>

You're receiving this because you joined the waitlist for this item. We'll only send this once.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
