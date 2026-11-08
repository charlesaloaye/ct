<?php

namespace App\Mail;

use App\Models\Article;
use App\Models\Subscriber;
use App\Support\SiteSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;

class ArticleNewsletter extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    /** Skip quietly if the record was deleted before the queue got to it. */
    public bool $deleteWhenMissingModels = true;

    public function __construct(public Article $article, public Subscriber $subscriber)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->article->title);
    }

    public function headers(): Headers
    {
        return new Headers(text: [
            'List-Unsubscribe' => '<'.$this->unsubscribeUrl().'>',
            'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click',
        ]);
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.article-newsletter', with: [
            'articleUrl' => route('blog.show', $this->article->slug),
            'unsubscribeUrl' => $this->unsubscribeUrl(),
            'signature' => SiteSettings::value('identity.name') ?: config('app.name'),
            'siteName' => parse_url(config('app.url'), PHP_URL_HOST),
        ]);
    }

    private function unsubscribeUrl(): string
    {
        return route('subscribe.unsubscribe', $this->subscriber->unsubscribe_token);
    }
}
