<?php

namespace App\Jobs;

use App\Mail\ArticleNewsletter;
use App\Models\Article;
use App\Models\Subscriber;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Emails a newly published article to every confirmed subscriber, once.
 *
 * notified_at is claimed atomically before any mail is queued, so a job that is
 * dispatched twice (e.g. a save and the scheduler racing) still sends one email.
 */
class SendArticleNewsletter implements ShouldQueue
{
    use Queueable;

    public const CHUNK = 200;

    public function __construct(public int $articleId)
    {
    }

    public function handle(): void
    {
        $article = Article::query()->find($this->articleId);

        if (! $article || ! $article->notify_subscribers || ! $article->isLive()) {
            return;
        }

        $claimed = Article::query()
            ->whereKey($article->id)
            ->whereNull('notified_at')
            ->update(['notified_at' => now()]);

        if ($claimed === 0) {
            return;
        }

        $sent = 0;
        Subscriber::query()->confirmed()->chunkById(self::CHUNK, function ($subscribers) use ($article, &$sent) {
            foreach ($subscribers as $subscriber) {
                Mail::to($subscriber->email)->queue(new ArticleNewsletter($article, $subscriber));
                $sent++;
            }
        });

        Log::info('Article newsletter queued', ['article' => $article->slug, 'recipients' => $sent]);
    }
}
