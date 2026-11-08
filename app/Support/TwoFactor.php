<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Str;

/** Checks a login's second factor: an authenticator code, or a one-time recovery code. */
final class TwoFactor
{
    public const RECOVERY_CODES = 8;

    public static function passes(User $user, string $code): bool
    {
        $code = trim($code);

        if (preg_match('/^\d{3}\s?\d{3}$/', $code)) {
            return Totp::verify($user->two_factor_secret, $code);
        }

        // Recovery codes work once each.
        $codes = $user->two_factor_recovery_codes ?? [];
        $match = collect($codes)->first(fn (string $c) => hash_equals($c, strtolower($code)));

        if ($match === null) {
            return false;
        }

        $user->forceFill(['two_factor_recovery_codes' => array_values(array_diff($codes, [$match]))])->save();

        return true;
    }

    /** @return list<string> */
    public static function newRecoveryCodes(): array
    {
        return collect(range(1, self::RECOVERY_CODES))
            ->map(fn () => Str::lower(Str::random(5).'-'.Str::random(5)))
            ->all();
    }
}
