<?php

namespace App\Mail;

use App\Models\Subscriber;
use App\Support\SiteSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ConfirmSubscription extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    /** Skip quietly if the record was deleted before the queue got to it. */
    public bool $deleteWhenMissingModels = true;

    public function __construct(public Subscriber $subscriber)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Confirm your subscription to '.parse_url(config('app.url'), PHP_URL_HOST));
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.confirm-subscription', with: [
            'confirmUrl' => route('subscribe.confirm', $this->subscriber->confirm_token),
            'signature' => SiteSettings::value('identity.name') ?: config('app.name'),
            'siteName' => parse_url(config('app.url'), PHP_URL_HOST),
        ]);
    }
}
