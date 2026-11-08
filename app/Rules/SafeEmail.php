<?php

namespace App\Rules;

/**
 * Email validation that is safe to use in mail headers.
 *
 * Laravel 11's "email" rule has a published advisory (CRLF injection) that is
 * only fixed in 12.60+. Rejecting any control character here closes it for
 * every address that reaches a To or Reply-To header.
 */
final class SafeEmail
{
    /** @return list<string> */
    public static function rules(): array
    {
        return ['string', 'max:190', 'email:rfc', 'not_regex:/[\x00-\x1F\x7F]/'];
    }
}
