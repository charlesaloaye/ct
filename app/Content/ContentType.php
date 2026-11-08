<?php

namespace App\Content;

use App\Support\MediaUrl;
use App\Support\Status;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Describes one admin content type: its model, validation, table row and search.
 * Input and output keys are camelCase versions of the column names.
 */
abstract class ContentType
{
    /** @return class-string<Model> */
    abstract public function model(): string;

    /** Validation rules for a create; updates make every rule "sometimes". */
    abstract protected function fields(): array;

    /** @return array{title: string, meta: ?string} */
    abstract public function row(Model $item): array;

    /** @return list<string> */
    abstract public function searchColumns(): array;

    /** @return list<string> */
    public function statuses(): array
    {
        return Status::CONTENT;
    }

    public function rules(bool $creating): array
    {
        $rules = $this->fields() + [
            'status' => ['string', Rule::in($this->statuses())],
            'sortOrder' => ['integer', 'min:0'],
        ];

        if ($creating) {
            return $rules;
        }

        // Partial updates: validate only what was sent ("required" still means non-empty).
        return collect($rules)
            ->map(fn ($rule, $key) => str_contains($key, '.') ? $rule : ['sometimes', ...(array) $rule])
            ->all();
    }

    /** Validated camelCase input -> snake_case attributes. */
    public function attributes(array $validated): array
    {
        return collect($validated)->mapWithKeys(fn ($v, $k) => [Str::snake($k) => $v])->all();
    }

    public function newQuery(): Builder
    {
        return ($this->model())::query();
    }

    public function applyStatusFilter(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function search(Builder $query, string $term): Builder
    {
        $like = '%'.addcslashes($term, '%_\\').'%';

        return $query->where(function (Builder $q) use ($like) {
            foreach ($this->searchColumns() as $column) {
                $q->orWhere($column, 'like', $like);
            }
        });
    }

    public function statusLabel(Model $item): string
    {
        return Status::label($item->status);
    }

    /** Status to use when "Show" is clicked on a hidden item. */
    public function visibleStatus(): string
    {
        return Status::PUBLISHED;
    }

    /** Extra fields for the editor (URLs for stored paths, relation names). */
    public function extras(Model $item): array
    {
        return [];
    }

    public function with(): array
    {
        return [];
    }

    protected function urlFor(?string $path): ?string
    {
        return MediaUrl::for($path);
    }
}
