<?php

use App\Support\Totp;

// RFC 6238 appendix B test vector: ASCII "12345678901234567890" (base32 below), SHA-1.
const RFC_SECRET = 'GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQ';

it('matches the RFC 6238 test vectors', function (int $time, string $code) {
    expect(Totp::code(RFC_SECRET, $time))->toBe($code);
})->with([
    [59, '287082'],
    [1111111109, '081804'],
    [1234567890, '005924'],
    [2000000000, '279037'],
]);

it('accepts one step of clock drift and nothing more', function () {
    $secret = Totp::generateSecret();
    $now = 1_700_000_000;

    expect(Totp::verify($secret, Totp::code($secret, $now - 30), $now))->toBeTrue()
        ->and(Totp::verify($secret, Totp::code($secret, $now + 30), $now))->toBeTrue()
        ->and(Totp::verify($secret, Totp::code($secret, $now - 90), $now))->toBeFalse()
        ->and(Totp::verify($secret, 'abcdef', $now))->toBeFalse();
});

it('builds an otpauth uri', function () {
    expect(Totp::uri('ABC', 'me@example.com', 'charlestechy'))
        ->toBe('otpauth://totp/charlestechy:me%40example.com?secret=ABC&issuer=charlestechy&algorithm=SHA1&digits=6&period=30');
});
