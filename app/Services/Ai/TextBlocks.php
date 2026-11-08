<?php

namespace App\Services\Ai;

use Illuminate\Support\Str;

/**
 * Turns the model's plain-text article (## headings, > quotes, ``` fences,
 * blank-line paragraphs) into the body blocks the blog renders, and enforces
 * house style: plain hyphens, never em or en dashes.
 */
class TextBlocks
{
    public static function plainDashes(string $text): string
    {
        // " — " and " – " become " - "; tight "a—b" becomes "a - b".
        $text = preg_replace('/\s*[\x{2014}\x{2013}]\s*/u', ' - ', $text);

        return preg_replace('/[ \t]+$/m', '', $text);
    }

    /** @return list<array{type: string, text: string, id?: string, toc?: string}> */
    public static function parse(string $text): array
    {
        $text = str_replace("\r\n", "\n", self::plainDashes(trim($text)));
        $blocks = [];
        $ids = [];

        // Code fences first, so blank lines inside code don't split it.
        $parts = preg_split('/^```[^\n]*\n(.*?)^```[ \t]*$/ms', $text, -1, PREG_SPLIT_DELIM_CAPTURE);

        foreach ($parts as $i => $part) {
            if ($i % 2 === 1) {
                $blocks[] = ['type' => 'code', 'text' => rtrim($part, "\n")];

                continue;
            }

            // A heading line followed directly by text becomes its own block.
            $part = preg_replace('/^(#{1,6}[ \t]+[^\n]+)\n(?!\n)/m', "$1\n\n", $part);

            foreach (preg_split('/\n\s*\n/', $part) as $chunk) {
                $chunk = trim($chunk);
                if ($chunk === '') {
                    continue;
                }

                if (preg_match('/^#{1,6}[ \t]+([^\n]+)$/', $chunk, $m)) {
                    $heading = trim($m[1], " #*\t");
                    $id = Str::slug($heading) ?: 'section';
                    $base = $id;
                    $n = 2;
                    while (in_array($id, $ids, true)) {
                        $id = $base.'-'.$n++;
                    }
                    $ids[] = $id;
                    $blocks[] = ['type' => 'h2', 'text' => $heading, 'id' => $id];

                    continue;
                }

                if (str_starts_with($chunk, '>')) {
                    $blocks[] = ['type' => 'quote', 'text' => trim(preg_replace('/^>\s?/m', '', $chunk))];

                    continue;
                }

                $blocks[] = ['type' => 'p', 'text' => preg_replace('/\s*\n\s*/', ' ', $chunk)];
            }
        }

        return $blocks;
    }

    /** Body blocks back to plain text, for prompts that need the article content. */
    public static function toText(array $blocks): string
    {
        return collect($blocks)->map(fn ($b) => match ($b['type'] ?? 'p') {
            'h2' => '## '.$b['text'],
            'quote' => '> '.$b['text'],
            'code' => "```\n".$b['text']."\n```",
            default => $b['text'] ?? '',
        })->join("\n\n");
    }
}
