<?php

namespace App\Content;

use App\Models\Venture;
use Illuminate\Database\Eloquent\Model;

class VentureType extends ContentType
{
    public function model(): string
    {
        return Venture::class;
    }

    protected function fields(): array
    {
        return [
            'name' => ['required', 'string', 'max:160'],
            'slug' => ['nullable', 'alpha_dash', 'max:160'],
            'role' => ['required', 'string', 'max:80'],
            'isFounder' => ['boolean'],
            'description' => ['nullable', 'string', 'max:2000'],
            'url' => ['nullable', 'url:http,https', 'max:255'],
            'logoPath' => ['nullable', 'string', 'max:255'],
            'years' => ['nullable', 'string', 'max:40'],
        ];
    }

    public function row(Model $item): array
    {
        return ['title' => $item->name, 'meta' => $item->role];
    }

    public function searchColumns(): array
    {
        return ['name', 'role'];
    }

    public function extras(Model $item): array
    {
        return ['logoUrl' => $this->urlFor($item->logo_path)];
    }
}
