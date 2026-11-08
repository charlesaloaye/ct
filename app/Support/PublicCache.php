<?php

namespace App\Support;

use Closure;
use Illuminate\Support\Facades\Cache;

/**
 * Caches public API payloads for five minutes. Any content write bumps the
 * version number, so every public key is invalidated at once without needing
 * cache tags (works with the database, file and Redis stores alike).
 */
class PublicCache
{
    public const TTL = 300;

    private const VERSION_KEY = 'public-cache:version';

    public static function remember(string $key, Closure $callback): mixed
    {
        return Cache::remember(self::key($key), self::TTL, $callback);
    }

    public static function flush(): void
    {
        Cache::forever(self::VERSION_KEY, self::version() + 1);
    }

    public static function key(string $key): string
    {
        return 'public-cache:v'.self::version().':'.$key;
    }

    private static function version(): int
    {
        return (int) Cache::get(self::VERSION_KEY, 1);
    }
}
