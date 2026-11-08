<?php

namespace App\Http\Controllers\Admin;

use App\Content\ArticleType;
use App\Content\ContentType;
use App\Content\ContentTypes;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ContentRequest;
use App\Http\Requests\Admin\IndexRequest;
use App\Http\Requests\Admin\ReorderRequest;
use App\Http\Requests\Admin\VisibilityRequest;
use App\Http\Resources\Admin\ContentItemResource;
use App\Http\Resources\Admin\ContentRowList;
use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\Project;
use App\Models\Venture;
use App\Support\PublicCache;
use App\Support\Status;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

/**
 * List, create, edit, reorder, hide and delete for projects, products,
 * articles, services, experience and testimonials. The {type} comes from the
 * route defaults (see routes/web.php).
 */
class ContentController extends Controller
{
    public function index(IndexRequest $request): Response
    {
        $type = $this->type($request);

        $items = $type->newQuery()
            ->with($type->with())
            ->when($request->validated('q'), fn ($q, $term) => $type->search($q, $term))
            ->when($request->validated('status'), fn ($q, $status) => $type->applyStatusFilter($q, $status))
            ->ordered()
            ->limit(500)
            ->get();

        return Inertia::render('admin/Collection', [
            'type' => $request->route('type'),
            'rows' => (new ContentRowList($items, $type))->resolve($request),
            'total' => $type->newQuery()->count(),
            'filters' => ['q' => (string) $request->validated('q'), 'status' => $request->input('status') ?: 'All'],
            'statuses' => array_map(Status::label(...), $type->statuses()),
            'meta' => ['title' => ucfirst($request->route('type')).' · Admin'],
        ]);
    }

    public function create(Request $request): Response
    {
        return $this->editor($request, null);
    }

    public function edit(Request $request): Response
    {
        return $this->editor($request, $this->find($request));
    }

    public function store(ContentRequest $request): RedirectResponse
    {
        $type = $request->type();
        $model = $type->model();
        $item = new $model();
        $item->fill($request->attributesForModel());
        $item->status ??= Status::DRAFT;
        $item->save();

        Inertia::flash('toast', $this->savedMessage($type, $item->fresh()));

        return redirect()->route("admin.{$request->route('type')}.edit", $item->getKey());
    }

    public function update(ContentRequest $request): RedirectResponse
    {
        $item = $this->find($request);
        $item->fill($request->attributesForModel())->save();

        Inertia::flash('toast', $this->savedMessage($request->type(), $item->fresh()));

        return back();
    }

    public function destroy(Request $request): RedirectResponse
    {
        $item = $this->find($request);
        $item->delete();

        Inertia::flash('toast', '"'.$this->title($this->type($request), $item).'" deleted');

        return redirect()->route("admin.{$request->route('type')}.index");
    }

    public function reorder(ReorderRequest $request): RedirectResponse
    {
        $type = $this->type($request);
        $ids = $request->validated('ids');

        DB::transaction(function () use ($type, $ids) {
            foreach ($ids as $position => $id) {
                $type->newQuery()->whereKey($id)->update(['sort_order' => $position + 1]);
            }
        });
        PublicCache::flush();

        Inertia::flash('toast', 'Order saved');

        return back();
    }

    public function visibility(VisibilityRequest $request): RedirectResponse
    {
        $type = $this->type($request);
        $item = $this->find($request);
        $hide = $request->has('hidden') ? $request->boolean('hidden') : $item->status !== Status::HIDDEN;

        if ($hide && $item->status !== Status::HIDDEN) {
            $item->status_before_hidden = $item->status;
            $item->status = Status::HIDDEN;
        } elseif (! $hide && $item->status === Status::HIDDEN) {
            // Put it back as it was; items hidden before this was tracked go to published.
            $item->status = $item->status_before_hidden ?: $type->visibleStatus();
            $item->status_before_hidden = null;
        }
        $item->save();

        $title = $this->title($type, $item);
        Inertia::flash('toast', $hide ? "\"{$title}\" hidden from the site" : "\"{$title}\" is visible again");

        return back();
    }

    private function editor(Request $request, ?Model $item): Response
    {
        $key = $request->route('type');
        $type = $this->type($request);

        $options = match ($key) {
            'articles' => ['categories' => ArticleCategory::query()->ordered()->get(['id', 'name'])],
            'testimonials' => ['projects' => Project::query()->ordered()->get(['id', 'title'])],
            'projects' => ['tags' => Project::query()->whereNotNull('tag')->distinct()->orderBy('tag')->pluck('tag')],
            'products' => ['ventures' => Venture::query()->ordered()->get(['id', 'name'])],
            default => [],
        };

        return Inertia::render($type instanceof ArticleType ? 'admin/ArticleEdit' : 'admin/ContentEdit', [
            'type' => $key,
            'item' => $item ? (new ContentItemResource($item, $type))->resolve($request) : null,
            'statuses' => array_map(Status::label(...), $type->statuses()),
            'nextSortOrder' => (int) $type->newQuery()->max('sort_order') + 1,
            'options' => $options,
            'meta' => ['title' => ($item ? 'Edit' : 'New').' · Admin'],
        ]);
    }

    private function savedMessage(ContentType $type, Model $item): string
    {
        if ($item instanceof Article) {
            return match ($item->status) {
                Status::PUBLISHED => 'Article published',
                'scheduled' => 'Article scheduled for '.$item->published_at->timezone(config('app.timezone'))->format('j M Y, H:i'),
                default => 'Draft saved',
            };
        }

        return '"'.$this->title($type, $item).'" saved';
    }

    private function title(ContentType $type, Model $item): string
    {
        return (string) ($type->row($item)['title'] ?: 'Item');
    }

    private function type(Request $request): ContentType
    {
        return ContentTypes::for($request->route('type'));
    }

    private function find(Request $request): Model
    {
        $type = $this->type($request);

        return $type->newQuery()->with($type->with())->findOrFail($request->route('id'));
    }
}
