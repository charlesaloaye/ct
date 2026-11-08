<?php

use App\Services\Ai\AnthropicWriter;
use App\Services\Ai\TextBlocks;

it('parses headings, quotes, code and paragraphs into blocks', function () {
    $text = "First para\nwraps here.\n\n## Day 1 — Get it running\nStraight after the heading.\n\n> A stalled project rarely needs a rewrite.\n\n```bash\ncomposer install\n\nphp artisan test\n```\n\n## Day 1 — Get it running\n\nLast.";

    expect(TextBlocks::parse($text))->toBe([
        ['type' => 'p', 'text' => 'First para wraps here.'],
        ['type' => 'h2', 'text' => 'Day 1 - Get it running', 'id' => 'day-1-get-it-running'],
        ['type' => 'p', 'text' => 'Straight after the heading.'],
        ['type' => 'quote', 'text' => 'A stalled project rarely needs a rewrite.'],
        ['type' => 'code', 'text' => "composer install\n\nphp artisan test"],
        ['type' => 'h2', 'text' => 'Day 1 - Get it running', 'id' => 'day-1-get-it-running-2'],
        ['type' => 'p', 'text' => 'Last.'],
    ]);
});

it('replaces em and en dashes with plain hyphens', function () {
    expect(TextBlocks::plainDashes('Fast—cheap – and good'))->toBe('Fast - cheap - and good');
});

it('round-trips blocks to text', function () {
    $blocks = [['type' => 'h2', 'text' => 'Why', 'id' => 'why'], ['type' => 'p', 'text' => 'Because.']];

    expect(TextBlocks::parse(TextBlocks::toText($blocks)))->toBe($blocks);
});

it('fits seo text to the limit on a word boundary', function () {
    expect(AnthropicWriter::fit(str_repeat('word ', 40), 60))->toHaveLength(59)
        ->and(AnthropicWriter::fit('Short enough', 60))->toBe('Short enough');
});
