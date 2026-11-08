<?php

namespace App\Content;

use App\Models\Experience;
use Illuminate\Database\Eloquent\Model;

class ExperienceType extends ContentType
{
    public function model(): string
    {
        return Experience::class;
    }

    protected function fields(): array
    {
        return [
            'org' => ['required', 'string', 'max:160'],
            'role' => ['required', 'string', 'max:120'],
            'years' => ['required', 'string', 'max:40'],
            'place' => ['nullable', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function row(Model $item): array
    {
        return ['title' => $item->org, 'meta' => $item->years];
    }

    public function searchColumns(): array
    {
        return ['org', 'role', 'place'];
    }
}
