<x-mail::message>
# New enquiry{{ $enquiry->need ? ': '.$enquiry->need : '' }}

**From:** {{ $enquiry->name }} ({{ $enquiry->email }})

{{ $enquiry->body }}

<x-mail::button :url="$inboxUrl">
Open the inbox
</x-mail::button>

Reply to this email to answer {{ $enquiry->name }} directly.
</x-mail::message>
