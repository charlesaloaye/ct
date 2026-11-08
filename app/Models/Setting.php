<?php

namespace App\Models;

use App\Models\Concerns\BustsPublicCache;
use Illuminate\Database\Eloquent\Model;

/**
 * One row per settings group (identity, hero, contact, ai, mail ...), value
 * stored as JSON. See App\Support\SiteSettings for the shape of each group.
 */
class Setting extends Model
{
    use BustsPublicCache;

    protected $fillable = ['key', 'value'];

    protected $casts = ['value' => 'json'];

    /** Loaded once per request (or job); reset whenever a setting is written. */
    private static ?array $loaded = null;

    protected static function booted(): void
    {
        static::saved(fn () => self::$loaded = null);
        static::deleted(fn () => self::$loaded = null);
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return static::allKeyed()[$key] ?? $default;
    }

    public static function put(string $key, mixed $value): self
    {
        return static::query()->updateOrCreate(['key' => $key], ['value' => $value]);
    }

    /** @return array<string, mixed> all settings keyed by name */
    public static function allKeyed(): array
    {
        return self::$loaded ??= static::query()->get()->mapWithKeys(fn (self $s) => [$s->key => $s->value])->all();
    }

    /** Forget the per-request copy (queue workers call this before each job). */
    public static function forgetLoaded(): void
    {
        self::$loaded = null;
    }
}
