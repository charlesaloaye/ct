<x-mail::message>
# {{ $article->title }}

{{ $article->excerpt }}

<x-mail::button :url="$articleUrl">
Read the article
</x-mail::button>

{{ $signature }}

<small>You get this because you subscribed on {{ $siteName }}. [Unsubscribe]({{ $unsubscribeUrl }})</small>
</x-mail::message>
