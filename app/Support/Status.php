<?php

namespace App\Support;

/**
 * Status values shared by content types. Articles use draft|scheduled|published|hidden
 * plus an is_featured flag; everything else uses featured|published|draft|hidden.
 */
final class Status
{
    public const FEATURED = 'featured';

    public const PUBLISHED = 'published';

    public const DRAFT = 'draft';

    public const HIDDEN = 'hidden';

    public const SCHEDULED = 'scheduled';

    public const CONTENT = [self::FEATURED, self::PUBLISHED, self::DRAFT, self::HIDDEN];

    public const ARTICLE = [self::DRAFT, self::SCHEDULED, self::PUBLISHED, self::HIDDEN];

    /** Statuses visible on the public site. */
    public const VISIBLE = [self::FEATURED, self::PUBLISHED];

    /** "Featured" (as shown in the admin) -> "featured". Accepts either form. */
    public static function normalise(?string $value): ?string
    {
        return $value === null ? null : strtolower(trim($value));
    }

    /** "featured" -> "Featured", the label the admin table and CSS classes use. */
    public static function label(string $value): string
    {
        return ucfirst($value);
    }
}
