<?php

namespace App\Content;

use App\Models\Appearance;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

/** Media & press: only real, independently published coverage. */
class AppearanceType extends ContentType
{
    public function model(): string
    {
        return Appearance::class;
    }

    protected function fields(): array
    {
        return [
            'title' => ['required', 'string', 'max:200'],
            'outlet' => ['required', 'string', 'max:120'],
            'kind' => ['required', Rule::in(Appearance::KINDS)],
            // Coverage must link to the source so it can be verified.
            'url' => ['required', 'url:http,https', 'max:255'],
            'publishedOn' => ['nullable', 'date'],
            'summary' => ['nullable', 'string', 'max:600'],
        ];
    }

    public function row(Model $item): array
    {
        return ['title' => $item->title, 'meta' => $item->outlet];
    }

    public function searchColumns(): array
    {
        return ['title', 'outlet'];
    }
}
