<?php

namespace App\Mail;

use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactReceived extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    /** Skip quietly if the record was deleted before the queue got to it. */
    public bool $deleteWhenMissingModels = true;

    public function __construct(public Message $enquiry)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: [new Address($this->enquiry->email, $this->enquiry->name)],
            subject: ($this->enquiry->need ?: 'New enquiry').' - '.$this->enquiry->name,
        );
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.contact-received', with: [
            'inboxUrl' => route('admin.messages.index', ['open' => $this->enquiry->id]),
        ]);
    }
}
