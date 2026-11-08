<?php

namespace App\Content;

use App\Models\Writing;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

/** "Writing elsewhere": links to articles published on other platforms. */
class WritingType extends ContentType
{
    public function model(): string
    {
        return Writing::class;
    }

    protected function fields(): array
    {
        return [
            'title' => ['required', 'string', 'max:200'],
            'publication' => ['required', 'string', 'max:80'],
            'url' => ['required', 'url:http,https', 'max:255', Rule::unique('writings', 'url')->ignore(request()->route('id'))],
            'publishedOn' => ['nullable', 'date'],
            'summary' => ['nullable', 'string', 'max:400'],
        ];
    }

    public function row(Model $item): array
    {
        return ['title' => $item->title, 'meta' => $item->publication];
    }

    public function searchColumns(): array
    {
        return ['title', 'publication'];
    }
}
