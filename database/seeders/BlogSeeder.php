<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\ArticleCategory;
use Carbon\CarbonImmutable;
use Database\Seeders\Concerns\ReadsSeedData;
use Illuminate\Database\Seeder;

class BlogSeeder extends Seeder
{
    use ReadsSeedData;

    public function run(): void
    {
        $blog = $this->data('blog');

        $categories = [];
        foreach (array_values(array_diff($blog['categories'], ['All'])) as $i => $name) {
            $categories[$name] = ArticleCategory::query()->create([
                'name' => $name,
                'tint' => $blog['tints'][$name] ?? '#EEECE6',
                'sort_order' => $i + 1,
            ])->id;
        }

        // Newest first, a week apart, ending on a fixed date so seeds are repeatable.
        $latest = CarbonImmutable::parse('2026-10-26 09:00:00');
        $live = 0;

        foreach ($blog['posts'] as $i => $post) {
            // Articles with a written body are live. The rest are planned topics: they stay drafts
            // (never shown publicly) until they are written in the editor.
            $written = ! empty($post['body']);

            Article::query()->create([
                'title' => $post['title'],
                'slug' => $post['slug'],
                'category_id' => $categories[$post['cat']],
                'excerpt' => $post['excerpt'],
                'body' => $post['body'] ?? null,
                'tags' => $post['tags'] ?? null,
                'is_featured' => $written && (bool) ($post['featured'] ?? false),
                'status' => $written ? 'published' : 'draft',
                'read_minutes' => (int) $post['read'],
                'published_at' => $written ? $latest->subWeeks($live++) : null,
                // Already live: never email existing subscribers about the back catalogue.
                'notify_subscribers' => false,
                'sort_order' => $i + 1,
            ]);
        }
    }
}
