<?php

namespace App\Models\Concerns;

use App\Support\PublicCache;

trait BustsPublicCache
{
    public static function bootBustsPublicCache(): void
    {
        static::saved(fn () => PublicCache::flush());
        static::deleted(fn () => PublicCache::flush());
    }
}
