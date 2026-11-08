<?php

use App\Mail\ContactReceived;
use App\Models\Message;
use App\Support\SiteSettings;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    seedSite();
    Mail::fake();
});

$valid = fn (array $extra = []) => $extra + ['name' => 'Test Visitor', 'email' => 'ada@example.com', 'need' => 'New web app', 'message' => 'We need a booking platform for our clinics.'];

it('saves the enquiry, emails the owner with reply-to set, and thanks the visitor', function () use ($valid) {
    // With no separate recipient configured, alerts go to the public contact address.
    config(['site.contact_recipient' => null]);

    $this->from('/')->post('/contact', $valid())->assertRedirect(url('/'));

    $message = Message::query()->sole();
    expect($message->only(['name', 'email', 'need']))->toBe(['name' => 'Test Visitor', 'email' => 'ada@example.com', 'need' => 'New web app']);

    Mail::assertQueued(ContactReceived::class, fn ($mail) => $mail->hasTo('hello@charlestechy.com')
        && $mail->hasReplyTo('ada@example.com')
        && str_contains($mail->render(), route('admin.messages.index', ['open' => $message->id])));
    expect(Inertia\Inertia::getFlashed())->toMatchArray(['contact' => "Thanks - your message is in. I'll reply within 24 hours."]);
});

it('sends enquiries to the recipient set in the admin', function () use ($valid) {
    SiteSettings::save('mail', ['contactRecipient' => 'inbox@example.com']);
    App\Support\RuntimeConfig::apply();

    $this->post('/contact', $valid());

    Mail::assertQueued(ContactReceived::class, fn ($mail) => $mail->hasTo('inbox@example.com'));
});

it('validates the fields', function (array $input, string $field) use ($valid) {
    $this->from('/')->post('/contact', $valid($input))->assertSessionHasErrors($field);
    expect(Message::query()->count())->toBe(0);
})->with([
    [['name' => ''], 'name'],
    [['email' => 'not-an-email'], 'email'],
    [['email' => "ada@example.com\r\nBcc: x@y.z"], 'email'],
    [['need' => 'Something not offered'], 'need'],
    [['message' => 'short'], 'message'],
]);

it('accepts the options set in Pages & SEO', function () use ($valid) {
    SiteSettings::save('sections', ['contact' => ['needs' => ['Consulting']]]);

    $this->post('/contact', $valid(['need' => 'Consulting']))->assertSessionHasNoErrors();
    $this->post('/contact', $valid(['need' => 'New web app']))->assertSessionHasErrors('need');
});

it('does not ask what is needed when no options are set', function () use ($valid) {
    SiteSettings::save('sections', ['contact' => ['needs' => []]]);

    $this->post('/contact', $valid(['need' => null]))->assertSessionHasNoErrors();
    expect(Message::query()->sole()->need)->toBeNull();
});

it('silently drops honeypot submissions', function () use ($valid) {
    $this->post('/contact', $valid(['website' => 'http://spam.example']))->assertRedirect();

    expect(Message::query()->count())->toBe(0);
    Mail::assertNothingQueued();
});

it('rate limits to 5 per hour, sending the visitor back with a note', function () use ($valid) {
    foreach (range(1, 5) as $i) {
        $this->post('/contact', $valid());
    }

    $this->from('/')->post('/contact', $valid())->assertRedirect(url('/'));
    expect(Message::query()->count())->toBe(5)
        ->and(Inertia\Inertia::getFlashed())->toMatchArray(['toast' => 'Too many attempts. Please wait a little and try again.']);
});
