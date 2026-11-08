<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TwoFactorRequest;
use App\Support\Totp;
use App\Support\TwoFactor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

/** Turn two-factor sign-in on (start + confirm with a code) and off (with the password). */
class TwoFactorController extends Controller
{
    /** Step 1: a fresh secret to add to the authenticator app. Not active until confirmed. */
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_if($user->hasTwoFactorEnabled(), 409, 'Two-factor sign-in is already on.');

        $user->forceFill([
            'two_factor_secret' => Totp::generateSecret(),
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        return back();
    }

    /** Step 2: prove the app is set up. The recovery codes are shown once (flash data). */
    public function confirm(TwoFactorRequest $request): RedirectResponse
    {
        $user = $request->user();
        abort_if($user->two_factor_secret === null, 409, 'Start two-factor setup first.');
        abort_if($user->hasTwoFactorEnabled(), 409, 'Two-factor sign-in is already on.');

        if (! Totp::verify($user->two_factor_secret, $request->validated('code'))) {
            throw ValidationException::withMessages(['code' => 'That code is not valid. Check the time on your phone and try the latest code.']);
        }

        $codes = TwoFactor::newRecoveryCodes();
        $user->forceFill(['two_factor_confirmed_at' => now(), 'two_factor_recovery_codes' => $codes])->save();

        Inertia::flash(['toast' => 'Two-factor sign-in is on', 'recoveryCodes' => $codes]);

        return back();
    }

    /** New recovery codes (the old ones stop working). Needs the password. */
    public function regenerate(Request $request): RedirectResponse
    {
        $request->validate(['password' => ['required', 'string', 'current_password:web']]);
        $user = $request->user();
        abort_unless($user->hasTwoFactorEnabled(), 409, 'Two-factor sign-in is off.');

        $codes = TwoFactor::newRecoveryCodes();
        $user->forceFill(['two_factor_recovery_codes' => $codes])->save();

        Inertia::flash(['toast' => 'New recovery codes created', 'recoveryCodes' => $codes]);

        return back();
    }

    public function destroy(TwoFactorRequest $request): RedirectResponse
    {
        $request->user()->forceFill(['two_factor_secret' => null, 'two_factor_recovery_codes' => null, 'two_factor_confirmed_at' => null])->save();
        Inertia::flash('toast', 'Two-factor sign-in turned off');

        return back();
    }
}
