<?php

use App\Support\Totp;
use Inertia\Testing\AssertableInertia as Assert;

it('updates the name and sign-in email', function () {
    $user = actingAsAdmin();

    $this->get('/admin/account')->assertInertia(fn (Assert $page) => $page->component('admin/Account')->where('profile.email', $user->email));
    $this->put('/admin/account/profile', ['name' => 'Charles A. Sedenu', 'email' => 'new@example.com'])->assertSessionHasNoErrors();

    expect($user->fresh()->only(['name', 'email']))->toBe(['name' => 'Charles A. Sedenu', 'email' => 'new@example.com']);
});

it('changes the password only with the current one, and enforces strength', function () {
    $user = actingAsAdmin(['password' => 'old-password-123']);

    $this->put('/admin/account/password', ['current_password' => 'wrong', 'password' => 'new-password-456', 'password_confirmation' => 'new-password-456'])->assertSessionHasErrors('current_password');
    $this->put('/admin/account/password', ['current_password' => 'old-password-123', 'password' => 'short', 'password_confirmation' => 'short'])->assertSessionHasErrors('password');
    $this->put('/admin/account/password', ['current_password' => 'old-password-123', 'password' => 'new-password-456', 'password_confirmation' => 'new-password-456'])->assertSessionHasNoErrors();

    expect(Hash::check('new-password-456', $user->fresh()->password))->toBeTrue();
});

it('sets up two-factor with a QR code, confirms it and shows recovery codes once', function () {
    $user = actingAsAdmin();

    $this->post('/admin/account/two-factor')->assertRedirect();
    $this->get('/admin/account')->assertInertia(fn (Assert $page) => $page
        ->where('twoFactor.pending', true)
        ->where('twoFactor.qrCode', fn ($qr) => str_starts_with($qr, 'data:image/svg+xml;base64,'))
        ->has('twoFactor.secret'));

    $this->post('/admin/account/two-factor/confirm', ['code' => '000000'])->assertSessionHasErrors('code');
    $this->post('/admin/account/two-factor/confirm', ['code' => Totp::code($user->fresh()->two_factor_secret)])->assertRedirect();

    expect($user->fresh()->hasTwoFactorEnabled())->toBeTrue()
        ->and(Inertia\Inertia::getFlashed()['recoveryCodes'])->toHaveCount(8);
    $this->get('/admin/account')->assertInertia(fn (Assert $page) => $page->where('twoFactor.enabled', true)->missing('twoFactor.secret'));
});

it('makes new recovery codes and turns two-factor off, both with the password', function () {
    $user = actingAsAdmin(['password' => 'pass-word-1234', 'two_factor_secret' => Totp::generateSecret(), 'two_factor_confirmed_at' => now(), 'two_factor_recovery_codes' => ['aaaaa-bbbbb']]);

    $this->post('/admin/account/two-factor/recovery-codes', ['password' => 'wrong'])->assertSessionHasErrors('password');
    $this->post('/admin/account/two-factor/recovery-codes', ['password' => 'pass-word-1234'])->assertRedirect();
    expect($user->fresh()->two_factor_recovery_codes)->toHaveCount(8)->not->toContain('aaaaa-bbbbb');

    $this->delete('/admin/account/two-factor', ['password' => 'wrong'])->assertSessionHasErrors('password');
    $this->delete('/admin/account/two-factor', ['password' => 'pass-word-1234'])->assertRedirect();
    expect($user->fresh()->hasTwoFactorEnabled())->toBeFalse();
});
