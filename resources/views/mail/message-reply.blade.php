<x-mail::message>
Hi {{ $enquiry->name }},

{{ $reply }}

{{ $signature }}

<x-mail::panel>
On {{ $sentAt }} you wrote:

{{ \Illuminate\Support\Str::limit($enquiry->body, 1200) }}
</x-mail::panel>
</x-mail::message>
