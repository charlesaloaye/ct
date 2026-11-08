<?php

use App\Mail\MessageReply;
use App\Models\Message;
use App\Models\Subscriber;
use Illuminate\Support\Facades\Mail;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(fn () => actingAsAdmin());

it('lists messages newest first and marks the open one read', function () {
    $old = Message::factory()->create(['created_at' => now()->subDay()]);
    $new = Message::factory()->create();

    $this->get('/admin/messages')->assertInertia(fn (Assert $page) => $page
        ->component('admin/Messages')
        ->where('messages.0.id', $new->id)
        ->where('openId', $new->id));
    expect($new->fresh()->read_at)->not->toBeNull()->and($old->fresh()->read_at)->toBeNull();

    $this->get("/admin/messages?open={$old->id}")->assertInertia(fn (Assert $page) => $page->where('openId', $old->id));
    expect($old->fresh()->read_at)->not->toBeNull();
});

it('searches messages', function () {
    Message::factory()->create(['name' => 'Test Visitor']);
    Message::factory()->create(['name' => 'Bola Ade']);

    $this->get('/admin/messages?q=visitor')->assertInertia(fn (Assert $page) => $page->has('messages', 1)->where('messages.0.name', 'Test Visitor'));
});

it('emails a reply to the sender and records it', function () {
    Mail::fake();
    seedSite();
    $message = Message::factory()->create(['email' => 'ada@example.com', 'need' => 'New web app']);

    $this->post("/admin/messages/{$message->id}/reply", ['reply' => ''])->assertSessionHasErrors('reply');
    $this->post("/admin/messages/{$message->id}/reply", ['reply' => 'Happy to help, Ada.'])->assertRedirect();

    Mail::assertQueued(MessageReply::class, fn ($mail) => $mail->hasTo('ada@example.com')
        && $mail->hasReplyTo('hello@charlestechy.com')
        && str_contains($mail->render(), 'Happy to help, Ada.')
        && $mail->envelope()->subject === 'Re: New web app');
    expect($message->fresh()->replied_at)->not->toBeNull()->and($message->fresh()->reply_body)->toBe('Happy to help, Ada.');
});

it('archives, restores and deletes', function () {
    $message = Message::factory()->create();

    $this->patch("/admin/messages/{$message->id}/archive")->assertRedirect('/admin/messages');
    $this->get('/admin/messages')->assertInertia(fn (Assert $page) => $page->has('messages', 0));
    $this->get('/admin/messages?archived=1')->assertInertia(fn (Assert $page) => $page->has('messages', 1));

    $this->patch("/admin/messages/{$message->id}/restore")->assertRedirect("/admin/messages?open={$message->id}");
    expect($message->fresh()->archived_at)->toBeNull();

    $this->delete("/admin/messages/{$message->id}")->assertRedirect('/admin/messages');
    expect(Message::query()->count())->toBe(0);
});

it('lists, filters, pages, exports and removes subscribers', function () {
    Subscriber::factory()->count(55)->create();
    $pending = Subscriber::factory()->unconfirmed()->create(['email' => 'pending@example.com']);

    $this->get('/admin/subscribers')->assertInertia(fn (Assert $page) => $page
        ->component('admin/Subscribers')
        ->has('subscribers', 50)
        ->where('pagination.total', 56)
        ->where('pagination.lastPage', 2)
        ->where('confirmed', 55));
    $this->get('/admin/subscribers?status=pending')->assertInertia(fn (Assert $page) => $page->has('subscribers', 1)->where('subscribers.0.email', 'pending@example.com'));

    $csv = $this->get('/admin/subscribers/export')->assertOk()->assertHeader('Content-Type', 'text/csv; charset=UTF-8')->streamedContent();
    expect(substr_count($csv, "\n"))->toBe(57)->and($csv)->toContain('pending@example.com');

    $this->delete("/admin/subscribers/{$pending->id}")->assertRedirect();
    expect(Subscriber::query()->count())->toBe(55);
});
