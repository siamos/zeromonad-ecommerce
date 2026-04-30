<x-mail::message>
# It's back in stock!

Hi there,

Good news — **{{ $product->name }}** is available again. You asked us to let you know, so here we are!

<x-mail::panel>
**{{ $product->name }}**

@if ($product->short_description)
{{ is_array($product->short_description) ? ($product->short_description['en'] ?? '') : $product->short_description }}
@endif

**Price: {{ number_format($product->price, 2) }} €**
</x-mail::panel>

Stock can go fast — grab yours before it sells out again.

<x-mail::button :url="url('/shop/' . $product->slug)">
Shop Now
</x-mail::button>

You're receiving this because you signed up for a back-in-stock alert. We won't send this more than once.

Thanks,<br>
{{ $siteName }}
</x-mail::message>
