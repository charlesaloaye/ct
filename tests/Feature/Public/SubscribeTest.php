<?php

use App\Mail\ConfirmSubscription;
use App\Models\Subscriber;
use Illuminate\Support\Facades\Mail;

beforeEach(fn () => Mail::fake());

it('creates an unconfirmed subscriber and emails a confirmation link', function () {
    $this->from('/blog')->post('/subscribe', ['email' => 'Reader@Example.com'])->assertRedirect(url('/blog'));

    $subscriber = Subscriber::query()->sole();
    expect($subscriber->email)->toBe('reader@example.com')->and($subscriber->confirmed_at)->toBeNull();
    Mail::assertQueued(ConfirmSubscription::class, fn ($mail) => $mail->hasTo('reader@example.com')
        && str_contains($mail->render(), route('subscribe.confirm', $subscriber->confirm_token)));
    expect(Inertia\Inertia::getFlashed())->toMatchArray(['subscribe' => 'Almost there - check your inbox to confirm.']);
});

it('confirms through the emailed link and lands on the blog', function () {
    $subscriber = Subscriber::factory()->unconfirmed()->create(['confirm_token' => 'tok123']);

    $this->get('/subscribe/confirm/tok123')->assertRedirect('/blog?subscription=confirmed');
    expect($subscriber->fresh()->confirmed_at)->not->toBeNull();

    $this->get('/subscribe/confirm/tok123')->assertRedirect('/blog?subscription=invalid');
});

it('does not resend to an address that is already confirmed', function () {
    Subscriber::factory()->create(['email' => 'reader@example.com']);

    $this->post('/subscribe', ['email' => 'reader@example.com']);

    Mail::assertNothingQueued();
    expect(Subscriber::query()->count())->toBe(1);
});

it('unsubscribes with the token (GET from the email, POST one-click)', function () {
    $a = Subscriber::factory()->create();
    $b = Subscriber::factory()->create();

    $this->get("/unsubscribe/{$a->unsubscribe_token}")->assertRedirect('/blog?subscription=removed');
    $this->post("/unsubscribe/{$b->unsubscribe_token}")->assertRedirect('/blog?subscription=removed');
    $this->get('/unsubscribe/nope')->assertRedirect('/blog?subscription=invalid');

    expect(Subscriber::query()->count())->toBe(0);
});

it('validates, drops bots and rate limits signups', function () {
    $this->post('/subscribe', ['email' => 'nope'])->assertSessionHasErrors('email');
    $this->post('/subscribe', ['email' => "a@b.co\r\nBcc: x@y.z"])->assertSessionHasErrors('email');

    $this->post('/subscribe', ['email' => 'bot@example.com', 'website' => 'x']);
    expect(Subscriber::query()->count())->toBe(0);

    // Five attempts an hour per IP: three are used above, so two more get through.
    foreach (range(1, 4) as $i) {
        $this->post('/subscribe', ['email' => "r{$i}@example.com"]);
    }
    expect(Subscriber::query()->pluck('email')->all())->toBe(['r1@example.com', 'r2@example.com']);
});
