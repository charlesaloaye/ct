<?php

namespace App\Support;

use App\Content\ContentTypes;
use App\Models\Article;
use App\Models\Message;
use App\Models\Subscriber;

/** Item counts per content type (by status) and inbox numbers for the admin sidebar and overview. */
final class AdminCounts
{
    public static function get(): array
    {
        $counts = [];
        foreach (array_keys(ContentTypes::TYPES) as $type) {
            $byStatus = ContentTypes::for($type)->newQuery()->toBase()
                ->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

            $counts[$type] = [
                'total' => (int) $byStatus->sum(),
                'featured' => (int) ($byStatus['featured'] ?? 0),
                'published' => (int) ($byStatus['published'] ?? 0),
                'draft' => (int) ($byStatus['draft'] ?? 0),
                'hidden' => (int) ($byStatus['hidden'] ?? 0),
                'scheduled' => (int) ($byStatus['scheduled'] ?? 0),
            ];
        }
        // Articles mark "featured" with a flag rather than a status.
        $counts['articles']['featured'] = Article::query()->where('is_featured', true)->count();

        return [
            'content' => $counts,
            'unreadMessages' => Message::query()->inbox()->unread()->count(),
            'inboxMessages' => Message::query()->inbox()->count(),
            'confirmedSubscribers' => Subscriber::query()->confirmed()->count(),
        ];
    }
}
