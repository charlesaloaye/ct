<?php

namespace App\Content;

use App\Models\Testimonial;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TestimonialType extends ContentType
{
    public function model(): string
    {
        return Testimonial::class;
    }

    protected function fields(): array
    {
        return [
            'quote' => ['required', 'string', 'max:1000'],
            'clientName' => ['required', 'string', 'max:120'],
            'clientRole' => ['nullable', 'string', 'max:120'],
            'company' => ['nullable', 'string', 'max:120'],
            'projectId' => ['nullable', 'integer', 'exists:projects,id'],
            'avatarPath' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function row(Model $item): array
    {
        return [
            'title' => Str::limit($item->quote, 70),
            'meta' => collect([$item->client_name, $item->company])->filter()->join(', '),
        ];
    }

    public function searchColumns(): array
    {
        return ['quote', 'client_name', 'company'];
    }

    public function extras(Model $item): array
    {
        return ['avatarUrl' => $this->urlFor($item->avatar_path)];
    }
}
