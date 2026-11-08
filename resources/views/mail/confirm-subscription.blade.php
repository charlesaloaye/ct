<x-mail::message>
# One more step

Confirm your email and you'll get one message each time a new article goes up on {{ $siteName }}. No spam.

<x-mail::button :url="$confirmUrl">
Confirm subscription
</x-mail::button>

If you didn't sign up, ignore this email and nothing will happen.

{{ $signature }}
</x-mail::message>
