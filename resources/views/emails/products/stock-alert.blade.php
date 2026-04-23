<x-mail::message>
# Great news — it's back in stock!

Hi there,

You asked us to let you know when **{{ $product->name }}** became available again. Good news — it's back!

<x-mail::panel>
**{{ $product->name }}**
@if ($product->short_description)
{{ $product->short_description }}
@endif
</x-mail::panel>

Don't wait too long — stock can go fast.

<x-mail::button :url="url('/shop/' . $product->slug)">
View Product
</x-mail::button>

Thanks,<br>
{{ $siteName }}
</x-mail::message>
