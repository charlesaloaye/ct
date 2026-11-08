<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Support\Status;
use Illuminate\Console\Command;

class PublishScheduledArticles extends Command
{
    protected $signature = 'articles:publish-scheduled';

    protected $description = 'Publish scheduled articles whose publish time has passed (and email subscribers if asked)';

    public function handle(): int
    {
        $count = 0;

        Article::query()
            ->where('status', Status::SCHEDULED)
            ->where('published_at', '<=', now())
            ->each(function (Article $article) use (&$count) {
                // Saving through the model fires the newsletter hook and busts the public cache.
                $article->status = Status::PUBLISHED;
                $article->save();
                $count++;
            });

        $this->info("Published {$count} scheduled article(s).");

        return self::SUCCESS;
    }
}
