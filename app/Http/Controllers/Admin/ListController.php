<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ArticleCategory;
use App\Models\Client;
use App\Models\ProcessStep;
use App\Models\RescueStep;
use App\Models\StackGroup;
use App\Models\Stat;
use App\Support\MediaUrl;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The short lists on the homepage and blog (stats, client names, rescue and
 * process steps, toolkit groups, blog categories), each edited as a whole:
 * rows are updated, added, removed and reordered in one save.
 */
class ListController extends Controller
{
    /** list key => [model, field => [column, rules]] */
    private const LISTS = [
        'stats' => [Stat::class, [
            'value' => ['value', ['required', 'string', 'max:20']],
            'label' => ['label', ['required', 'string', 'max:80']],
            'shortLabel' => ['short_label', ['nullable', 'string', 'max:40']],
        ]],
        'clients' => [Client::class, [
            'name' => ['name', ['required', 'string', 'max:80']],
            'logoPath' => ['logo_path', ['nullable', 'string', 'max:255']],
        ]],
        'rescue' => [RescueStep::class, [
            'number' => ['number', ['nullable', 'string', 'max:8']],
            'title' => ['title', ['required', 'string', 'max:120']],
            'description' => ['description', ['nullable', 'string', 'max:500']],
        ]],
        'process' => [ProcessStep::class, [
            'number' => ['number', ['nullable', 'string', 'max:8']],
            'title' => ['title', ['required', 'string', 'max:120']],
            'description' => ['description', ['nullable', 'string', 'max:500']],
        ]],
        'stack' => [StackGroup::class, [
            'label' => ['label', ['required', 'string', 'max:60']],
            'items' => ['items', ['present', 'array', 'max:40']],
            'items.*' => [null, ['required', 'string', 'max:40']],
        ]],
        'categories' => [ArticleCategory::class, [
            'name' => ['name', ['required', 'string', 'max:60', 'distinct:ignore_case']],
            'tint' => ['tint', ['required', 'regex:/^#[0-9A-Fa-f]{6}$/']],
        ]],
    ];

    public function index(): Response
    {
        $lists = [];
        foreach (self::LISTS as $key => [$model, $fields]) {
            $lists[$key] = $model::query()->ordered()->get()->map(fn (Model $row) => ['id' => $row->getKey()] + collect($fields)
                ->filter(fn ($f) => $f[0] !== null)
                ->map(fn ($f) => $row->getAttribute($f[0]))
                ->all() + ($key === 'clients' ? ['logoUrl' => MediaUrl::for($row->logo_path)] : []))->all();
        }

        $lists['categoryUsage'] = ArticleCategory::query()->withCount('articles')->pluck('articles_count', 'id');

        return Inertia::render('admin/Lists', ['lists' => $lists, 'meta' => ['title' => 'Homepage lists · Admin']]);
    }

    public function update(Request $request, string $list): RedirectResponse
    {
        abort_unless(isset(self::LISTS[$list]), 404);
        [$model, $fields] = self::LISTS[$list];

        $rules = ['items' => ['present', 'array', 'max:60'], 'items.*.id' => ['nullable', 'integer']];
        foreach ($fields as $field => [, $fieldRules]) {
            $rules["items.*.{$field}"] = $fieldRules;
        }
        $items = $request->validate($rules)['items'];

        DB::transaction(function () use ($model, $fields, $items) {
            $keep = [];
            foreach (array_values($items) as $position => $item) {
                $row = isset($item['id']) ? $model::query()->find($item['id']) : null;
                $row ??= new $model();

                foreach ($fields as $field => [$column]) {
                    if ($column !== null) {
                        $row->setAttribute($column, $item[$field] ?? null);
                    }
                }
                $row->setAttribute('sort_order', $position + 1);
                $row->save();
                $keep[] = $row->getKey();
            }

            // Removed rows. Articles in a removed category keep their text and lose the category.
            $model::query()->whereKeyNot($keep)->get()->each->delete();
        });

        Inertia::flash('toast', 'List saved');

        return back();
    }
}
