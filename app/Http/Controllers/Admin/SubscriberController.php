<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\IndexRequest;
use App\Http\Resources\Admin\SubscriberResource;
use App\Models\Subscriber;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SubscriberController extends Controller
{
    public function index(IndexRequest $request): Response
    {
        $status = $request->input('status');

        $subscribers = Subscriber::query()
            ->when($request->validated('q'), fn ($q, $term) => $q->where('email', 'like', '%'.addcslashes($term, '%_\\').'%'))
            ->when($status === 'confirmed', fn ($q) => $q->confirmed())
            ->when($status === 'pending', fn ($q) => $q->whereNull('confirmed_at'))
            ->latest()->latest('id')
            ->paginate(50)
            ->withQueryString();

        return Inertia::render('admin/Subscribers', [
            'subscribers' => SubscriberResource::collection($subscribers->getCollection())->resolve($request),
            'pagination' => [
                'total' => $subscribers->total(),
                'page' => $subscribers->currentPage(),
                'lastPage' => $subscribers->lastPage(),
                'prev' => $subscribers->previousPageUrl(),
                'next' => $subscribers->nextPageUrl(),
            ],
            'confirmed' => Subscriber::query()->confirmed()->count(),
            'filters' => ['q' => (string) $request->validated('q'), 'status' => in_array($status, ['confirmed', 'pending'], true) ? $status : ''],
            'meta' => ['title' => 'Subscribers · Admin'],
        ]);
    }

    public function export(): StreamedResponse
    {
        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['email', 'confirmed_at', 'subscribed_at']);

            Subscriber::query()->orderBy('id')->chunkById(500, function ($chunk) use ($out) {
                foreach ($chunk as $s) {
                    fputcsv($out, [$s->email, $s->confirmed_at?->toDateTimeString(), $s->created_at?->toDateTimeString()]);
                }
            });

            fclose($out);
        }, 'subscribers-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function destroy(Subscriber $subscriber): RedirectResponse
    {
        $subscriber->delete();
        Inertia::flash('toast', "{$subscriber->email} removed");

        return back();
    }
}
