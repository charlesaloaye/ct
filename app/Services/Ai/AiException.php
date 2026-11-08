<?php

namespace App\Services\Ai;

use RuntimeException;

/** A failure the admin should see in plain words. $status is the HTTP status to return. */
class AiException extends RuntimeException
{
    public function __construct(string $message, public readonly int $status = 502)
    {
        parent::__construct($message);
    }

    public static function notConfigured(string $what): self
    {
        return new self("The AI assistant is not set up: add the {$what} in Admin → Integrations.", 503);
    }
}
