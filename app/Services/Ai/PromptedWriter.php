<?php

namespace App\Services\Ai;

use Illuminate\Support\Str;

/**
 * The provider-neutral half of the writing assistant: prompts, parsing and
 * house style. Subclasses only send one system + user message and return text.
 */
abstract class PromptedWriter implements AiWriter
{
    /**
     * Send one request. $schema (a JSON Schema object) asks for structured JSON output.
     *
     * @return array{text: string, model: string}
     */
    abstract protected function complete(string $system, string $user, string $purpose, ?array $schema = null): array;

    public function draft(string $title, string $prompt, string $tone, string $length): array
    {
        $words = Prompts::words($length);

        $text = $this->complete(
            Prompts::voice()."\n\n".Prompts::FORMAT,
            "Write a full article draft.\n\nTitle: {$title}\nTone: {$tone}\nTarget length: about {$words} words\n\nWhat it should cover:\n{$prompt}",
            'draft',
        )['text'];

        $blocks = TextBlocks::parse($text);
        throw_if($blocks === [], new AiException('The AI returned an empty draft. Try again.'));

        return $blocks;
    }

    public function rewrite(string $text, string $action, string $context = ''): string
    {
        $instruction = Prompts::REWRITES[$action] ?? throw new AiException("Unknown rewrite action [{$action}].", 422);
        $around = $context !== '' ? "\n\nThe article so far, for context only:\n".Str::limit($context, 12000) : '';

        $result = $this->complete(
            Prompts::voice()."\n\nReturn plain text only: no headings, no quotes around it, no commentary.",
            "{$instruction}{$around}\n\nParagraph:\n{$text}",
            "rewrite:{$action}",
        )['text'];

        $result = trim(TextBlocks::plainDashes($result));
        throw_if($result === '', new AiException('The AI returned nothing for that paragraph. Try again.'));

        return $result;
    }

    public function seo(string $title, string $excerpt, string $body): array
    {
        $json = $this->complete(
            Prompts::voice()."\n\nYou write search titles and meta descriptions: specific, honest, no clickbait, no brand name (it is added automatically).",
            "Write an SEO title (at most 60 characters) and meta description (at most 155 characters) for this article.\n\nTitle: {$title}\nExcerpt: {$excerpt}\n\nArticle:\n".Str::limit($body, 12000),
            'seo',
            [
                'type' => 'object',
                'properties' => [
                    'seoTitle' => ['type' => 'string', 'description' => 'At most 60 characters'],
                    'seoDescription' => ['type' => 'string', 'description' => 'At most 155 characters'],
                ],
                'required' => ['seoTitle', 'seoDescription'],
                'additionalProperties' => false,
            ],
        )['text'];

        $data = json_decode($json, true);
        throw_unless(is_array($data) && isset($data['seoTitle'], $data['seoDescription']), new AiException('The AI returned SEO text in an unexpected format. Try again.'));

        return [
            'seoTitle' => self::fit(TextBlocks::plainDashes($data['seoTitle']), 60),
            'seoDescription' => self::fit(TextBlocks::plainDashes($data['seoDescription']), 155),
        ];
    }

    public function ping(): string
    {
        return $this->complete('You are a connection test.', 'Reply with the single word OK.', 'ping')['model'];
    }

    /** Trim to a hard limit on a word boundary. */
    public static function fit(string $text, int $max): string
    {
        $text = trim(preg_replace('/\s+/', ' ', $text));
        if (mb_strlen($text) <= $max) {
            return $text;
        }

        $cut = mb_substr($text, 0, $max + 1);
        $cut = mb_substr($cut, 0, (int) (mb_strrpos($cut, ' ') ?: $max));

        return rtrim($cut, ' ,.;:-');
    }
}
