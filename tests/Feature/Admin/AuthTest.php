<?php

use App\Models\User;
use App\Support\Totp;
use App\Support\TwoFactor;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Route;
use Inertia\Testing\AssertableInertia as Assert;

function admin(array $attributes = []): User
{
    return User::factory()->admin()->create($attributes + ['email' => 'owner@example.com', 'password' => 'a-strong-password-123']);
}

it('shows the sign-in page and signs an admin in', function () {
    admin();
    $this->get('/admin/login')->assertOk()->assertInertia(fn (Assert $page) => $page->component('admin/auth/Login'));

    $this->post('/admin/login', ['email' => 'owner@example.com', 'password' => 'a-strong-password-123'])->assertRedirect('/admin');
    $this->assertAuthenticated();
});

it('sets the remember cookie for "keep me signed in"', function () {
    admin();
    $response = $this->post('/admin/login', ['email' => 'owner@example.com', 'password' => 'a-strong-password-123', 'remember' => true]);

    expect(collect($response->headers->getCookies())->contains(fn ($c) => str_starts_with($c->getName(), 'remember_web_')))->toBeTrue();
});

it('rejects a wrong password and non-admin accounts with the same message', function () {
    admin();
    User::factory()->create(['email' => 'user@example.com', 'password' => 'a-strong-password-123']);

    $this->post('/admin/login', ['email' => 'owner@example.com', 'password' => 'wrong'])->assertSessionHasErrors(['email' => 'Those details do not match an admin account.']);
    $this->post('/admin/login', ['email' => 'user@example.com', 'password' => 'a-strong-password-123'])->assertSessionHasErrors(['email' => 'Those details do not match an admin account.']);
    $this->assertGuest();
});

it('throttles sign-in to 5 attempts a minute', function () {
    admin();
    foreach (range(1, 5) as $i) {
        $this->post('/admin/login', ['email' => 'owner@example.com', 'password' => 'wrong']);
    }

    $this->from('/admin/login')->post('/admin/login', ['email' => 'owner@example.com', 'password' => 'a-strong-password-123'])->assertRedirect(url('/admin/login'));
    $this->assertGuest();
});

it('asks for the authenticator code after the password when two-factor is on', function () {
    $secret = Totp::generateSecret();
    admin(['two_factor_secret' => $secret, 'two_factor_confirmed_at' => now(), 'two_factor_recovery_codes' => ['aaaaa-bbbbb']]);

    $this->post('/admin/login', ['email' => 'owner@example.com', 'password' => 'a-strong-password-123'])->assertRedirect('/admin/two-factor-challenge');
    $this->assertGuest();
    $this->get('/admin/two-factor-challenge')->assertInertia(fn (Assert $page) => $page->component('admin/auth/TwoFactorChallenge'));

    $this->post('/admin/two-factor-challenge', ['code' => '000000'])->assertSessionHasErrors('code');
    $this->assertGuest();

    $this->post('/admin/two-factor-challenge', ['code' => Totp::code($secret)])->assertRedirect('/admin');
    $this->assertAuthenticated();
});

it('accepts each recovery code once', function () {
    $user = admin(['two_factor_secret' => Totp::generateSecret(), 'two_factor_confirmed_at' => now(), 'two_factor_recovery_codes' => ['aaaaa-bbbbb', 'ccccc-ddddd']]);

    expect(TwoFactor::passes($user, 'aaaaa-bbbbb'))->toBeTrue()
        ->and(TwoFactor::passes($user->fresh(), 'aaaaa-bbbbb'))->toBeFalse()
        ->and($user->fresh()->two_factor_recovery_codes)->toBe(['ccccc-ddddd']);
});

it('never shows the code step without a correct password, and it expires', function () {
    $this->get('/admin/two-factor-challenge')->assertRedirect('/admin/login');

    admin(['two_factor_secret' => Totp::generateSecret(), 'two_factor_confirmed_at' => now()]);
    $this->post('/admin/login', ['email' => 'owner@example.com', 'password' => 'a-strong-password-123']);
    $this->travel(6)->minutes();

    $this->get('/admin/two-factor-challenge')->assertRedirect('/admin/login');
});

it('signs out', function () {
    actingAsAdmin();
    $this->post('/admin/logout')->assertRedirect('/admin/login');
    $this->assertGuest();
});

it('sends guests to sign in and refuses non-admins on every admin route', function () {
    $routes = collect(Route::getRoutes())->filter(fn ($r) => str_starts_with($r->uri(), 'admin')
        && in_array('admin', $r->gatherMiddleware(), true));
    expect($routes->count())->toBeGreaterThan(40);

    $user = User::factory()->create();
    foreach ($routes as $route) {
        $method = $route->methods()[0];
        $uri = '/'.preg_replace(['/\{id\}/', '/\{[^}]+\}/'], ['1', 'x'], $route->uri());

        $this->call($method, $uri)->assertRedirect('/admin/login');
        $this->actingAs($user)->call($method, $uri)->assertForbidden();
        auth()->logout();
    }
});

it('emails a reset link to admins only, and resets the password', function () {
    Notification::fake();
    $owner = admin();
    $user = User::factory()->create(['email' => 'user@example.com']);

    $this->get('/admin/forgot-password')->assertInertia(fn (Assert $page) => $page->component('admin/auth/ForgotPassword'));
    $this->post('/admin/forgot-password', ['email' => 'user@example.com'])->assertRedirect();
    $this->post('/admin/forgot-password', ['email' => 'owner@example.com'])->assertRedirect();
    Notification::assertNothingSentTo($user);

    $token = null;
    Notification::assertSentTo($owner, ResetPassword::class, function ($n) use (&$token, $owner) {
        $token = $n->token;

        return str_contains($n->toMail($owner)->actionUrl, route('admin.password.reset', ['token' => $n->token, 'email' => 'owner@example.com']));
    });

    $this->get("/admin/reset-password/{$token}?email=owner@example.com")->assertInertia(fn (Assert $page) => $page
        ->component('admin/auth/ResetPassword')->where('email', 'owner@example.com'));

    $this->post('/admin/reset-password', ['token' => $token, 'email' => 'owner@example.com', 'password' => 'short', 'password_confirmation' => 'short'])->assertSessionHasErrors('password');
    $this->post('/admin/reset-password', ['token' => $token, 'email' => 'owner@example.com', 'password' => 'brand-new-password-42', 'password_confirmation' => 'brand-new-password-42'])->assertRedirect('/admin/login');

    expect(Hash::check('brand-new-password-42', $owner->fresh()->password))->toBeTrue();
    $this->post('/admin/reset-password', ['token' => $token, 'email' => 'owner@example.com', 'password' => 'another-password-42', 'password_confirmation' => 'another-password-42'])->assertSessionHasErrors('email');
});

it('keeps the admin out of search engines', function () {
    $this->get('/admin/login')->assertHeader('X-Robots-Tag', 'noindex, nofollow')->assertSee('<meta name="robots" content="noindex, nofollow">', false);
});
