<?php

use App\Jobs\SendArticleNewsletter;
use App\Mail\ArticleNewsletter;
use App\Models\Article;
use App\Models\Subscriber;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;

it('publishes scheduled articles once their time has passed', function () {
    $due = Article::factory()->create(['status' => 'scheduled', 'published_at' => now()->addHour()]);
    $later = Article::factory()->create(['status' => 'scheduled', 'published_at' => now()->addDays(2)]);

    $this->get("/blog/{$due->slug}")->assertNotFound();

    $this->travel(2)->hours();
    $this->artisan('articles:publish-scheduled')->assertSuccessful();

    expect($due->fresh()->status)->toBe('published')
        ->and($later->fresh()->status)->toBe('scheduled');
    $this->get("/blog/{$due->slug}")->assertOk();
});

it('is registered with the scheduler', function () {
    $this->artisan('schedule:list')->expectsOutputToContain('articles:publish-scheduled');
});

it('emails confirmed subscribers once when an article is published', function () {
    Mail::fake();
    Subscriber::factory()->count(3)->create();
    Subscriber::factory()->unconfirmed()->create();

    $article = Article::factory()->draft()->create(['notify_subscribers' => true]);
    Mail::assertNothingQueued();

    $article->update(['status' => 'published']);

    Mail::assertQueued(ArticleNewsletter::class, 3);
    expect($article->fresh()->notified_at)->not->toBeNull();

    // Further saves, and a duplicate job, send nothing more.
    $article->update(['title' => 'Edited after publishing']);
    (new SendArticleNewsletter($article->id))->handle();
    Mail::assertQueued(ArticleNewsletter::class, 3);
});

it('emails subscribers when a scheduled article goes live', function () {
    Mail::fake();
    Subscriber::factory()->create(['email' => 'reader@example.com']);
    $article = Article::factory()->create(['status' => 'scheduled', 'published_at' => now()->addMinutes(5), 'notify_subscribers' => true]);

    Mail::assertNothingQueued();
    $this->travel(10)->minutes();
    $this->artisan('articles:publish-scheduled');

    Mail::assertQueued(ArticleNewsletter::class, fn ($mail) => $mail->hasTo('reader@example.com') && $mail->article->is($article));
});

it('does not email when notify is off', function () {
    Mail::fake();
    Subscriber::factory()->create();

    Article::factory()->create(['notify_subscribers' => false]);
    Mail::assertNothingQueued();
});

it('queues the newsletter job and chunks subscribers', function () {
    Queue::fake();
    $article = Article::factory()->create(['notify_subscribers' => true]);
    Queue::assertPushed(SendArticleNewsletter::class, fn ($job) => $job->articleId === $article->id);

    Mail::fake();
    Subscriber::factory()->count(SendArticleNewsletter::CHUNK + 5)->create();
    (new SendArticleNewsletter($article->id))->handle();
    Mail::assertQueued(ArticleNewsletter::class, SendArticleNewsletter::CHUNK + 5);
});

it('renders the newsletter with an unsubscribe link', function () {
    $article = Article::factory()->create(['title' => 'Webhooks that never lie']);
    $subscriber = Subscriber::factory()->create();

    $mail = new ArticleNewsletter($article, $subscriber);
    $mail->assertSeeInHtml('Webhooks that never lie')
        ->assertSeeInHtml(route('subscribe.unsubscribe', $subscriber->unsubscribe_token))
        ->assertSeeInHtml(route('blog.show', $article->slug));
});
