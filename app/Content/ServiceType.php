<?php

namespace App\Content;

use App\Models\Service;
use Illuminate\Database\Eloquent\Model;

class ServiceType extends ContentType
{
    public function model(): string
    {
        return Service::class;
    }

    protected function fields(): array
    {
        return [
            'title' => ['required', 'string', 'max:160'],
            'number' => ['nullable', 'string', 'max:8'],
            'description' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function row(Model $item): array
    {
        return ['title' => $item->title, 'meta' => $item->number];
    }

    public function searchColumns(): array
    {
        return ['title', 'description'];
    }
}
