<?php

namespace App\Services\Ai;

/**
 * The writing assistant behind the article editor. Implementations call an AI
 * provider server-side; keys and prompts never reach the browser.
 */
interface AiWriter
{
    /**
     * A full article draft as body blocks: [{type: p|h2|code|quote, text, id?, toc?}].
     *
     * @return list<array{type: string, text: string, id?: string, toc?: string}>
     */
    public function draft(string $title, string $prompt, string $tone, string $length): array;

    /** Replacement text for one paragraph. $action: continue|clearer|example|shorten. */
    public function rewrite(string $text, string $action, string $context = ''): string;

    /** @return array{seoTitle: string, seoDescription: string} */
    public function seo(string $title, string $excerpt, string $body): array;

    /** A one-line round trip to prove the key and model work. Returns the model name used. */
    public function ping(): string;
}
