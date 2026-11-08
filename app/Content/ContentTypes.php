<?php

namespace App\Content;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class ContentTypes
{
    /** URL segment => definition. */
    public const TYPES = [
        'projects' => ProjectType::class,
        'products' => ProductType::class,
        'articles' => ArticleType::class,
        'services' => ServiceType::class,
        'experiences' => ExperienceType::class,
        'testimonials' => TestimonialType::class,
        'ventures' => VentureType::class,
        'appearances' => AppearanceType::class,
        'writings' => WritingType::class,
    ];

    public static function for(?string $type): ContentType
    {
        $class = self::TYPES[$type] ?? throw new NotFoundHttpException("Unknown content type [{$type}].");

        return app($class);
    }
}
