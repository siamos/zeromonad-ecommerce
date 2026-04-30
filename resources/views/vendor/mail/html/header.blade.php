@props(['url'])
<tr>
<td class="header" style="background-color: #4f46e5; padding: 20px 0;">
<a href="{{ $url }}" style="display: inline-block; color: #ffffff; font-size: 20px; font-weight: bold; text-decoration: none; letter-spacing: -0.5px;">
{!! $slot !!}
</a>
</td>
</tr>
