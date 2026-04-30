<x-mail::message>
# Welcome to {{ $siteName }}!

Hi {{ $user->name }},

We're thrilled to have you on board. Your account is all set up and ready to go.

<x-mail::button :url="url('/account')">
Go to My Account
</x-mail::button>

Here's what you can do right now:

- **Browse listings** and find your next experience
- **Track your orders** from your account dashboard
- **Save favourites** to your wishlist for later

If you have any questions, just reply to this email — we're happy to help.

Thanks,<br>
{{ $siteName }}
</x-mail::message>
