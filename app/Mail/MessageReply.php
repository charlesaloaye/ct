<?php

namespace App\Mail;

use App\Models\Message;
use App\Support\SiteSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** A reply written in the admin inbox, sent to the person who used the contact form. */
class MessageReply extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    /** Skip quietly if the record was deleted before the queue got to it. */
    public bool $deleteWhenMissingModels = true;

    public function __construct(public Message $enquiry, public string $reply)
    {
    }

    public function envelope(): Envelope
    {
        $contact = SiteSettings::value('contact.email');

        return new Envelope(
            replyTo: $contact ? [new Address($contact, SiteSettings::value('identity.name'))] : [],
            subject: 'Re: '.($this->enquiry->need ?: 'your enquiry'),
        );
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.message-reply', with: [
            'signature' => SiteSettings::value('identity.name') ?: config('app.name'),
            'sentAt' => $this->enquiry->created_at->timezone(config('app.timezone'))->format('j M Y'),
        ]);
    }
}
