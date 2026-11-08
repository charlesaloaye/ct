<?php

namespace App\Content;

use App\Models\Product;
use Illuminate\Database\Eloquent\Model;

class ProductType extends ContentType
{
    public function model(): string
    {
        return Product::class;
    }

    protected function fields(): array
    {
        return [
            'name' => ['required', 'string', 'max:160'],
            'ventureId' => ['nullable', 'integer', 'exists:ventures,id'],
            'logoPath' => ['nullable', 'string', 'max:255'],
            'mono' => ['nullable', 'string', 'max:4'],
            'role' => ['nullable', 'string', 'max:60'],
            'category' => ['nullable', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:2000'],
            'proof' => ['nullable', 'string', 'max:160'],
            'domain' => ['nullable', 'string', 'max:255'],
            'url' => ['nullable', 'url:http,https', 'max:255'],
        ];
    }

    public function row(Model $item): array
    {
        return ['title' => $item->name, 'meta' => $item->domain];
    }

    public function extras(Model $item): array
    {
        return ['logoUrl' => $this->urlFor($item->logo_path)];
    }

    public function searchColumns(): array
    {
        return ['name', 'domain', 'category'];
    }
}
