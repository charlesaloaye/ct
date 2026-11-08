<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MessageIndexRequest;
use App\Http\Resources\Admin\MessageResource;
use App\Mail\MessageReply;
use App\Models\Message;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

class MessageController extends Controller
{
    public function index(MessageIndexRequest $request): Response
    {
        $archived = $request->boolean('archived');

        $messages = Message::query()
            ->when($archived, fn ($q) => $q->whereNotNull('archived_at'), fn ($q) => $q->inbox())
            ->when($request->validated('q'), function ($q, $term) {
                $like = '%'.addcslashes($term, '%_\\').'%';
                $q->where(fn ($w) => $w->where('name', 'like', $like)->orWhere('email', 'like', $like)->orWhere('body', 'like', $like));
            })
            ->latest()->latest('id')
            ->limit(200)
            ->get();

        // Opening a message marks it read.
        $open = $messages->firstWhere('id', (int) $request->query('open')) ?? $messages->first();
        if ($open && $open->read_at === null) {
            $open->forceFill(['read_at' => now()])->save();
        }

        return Inertia::render('admin/Messages', [
            'messages' => MessageResource::collection($messages)->resolve($request),
            'openId' => $open?->id,
            'filters' => ['q' => (string) $request->validated('q'), 'archived' => $archived],
            'meta' => ['title' => 'Messages · Admin'],
        ]);
    }

    /** Emails the reply to the sender (reply-to is the site's contact address) and records it. */
    public function reply(Request $request, Message $message): RedirectResponse
    {
        $validated = $request->validate(['reply' => ['required', 'string', 'min:2', 'max:10000']]);

        Mail::to($message->email, $message->name)->queue(new MessageReply($message, $validated['reply']));
        $message->forceFill(['reply_body' => $validated['reply'], 'replied_at' => now(), 'read_at' => $message->read_at ?? now()])->save();

        Inertia::flash('toast', "Reply sent to {$message->email}");

        return back();
    }

    public function archive(Message $message): RedirectResponse
    {
        $message->forceFill(['archived_at' => now(), 'read_at' => $message->read_at ?? now()])->save();
        Inertia::flash('toast', 'Message archived');

        return redirect()->route('admin.messages.index');
    }

    public function restore(Message $message): RedirectResponse
    {
        $message->forceFill(['archived_at' => null])->save();
        Inertia::flash('toast', 'Message moved back to the inbox');

        return redirect()->route('admin.messages.index', ['open' => $message->id]);
    }

    public function destroy(Request $request, Message $message): RedirectResponse
    {
        $wasArchived = $message->archived_at !== null;
        $message->delete();
        Inertia::flash('toast', 'Message deleted');

        return redirect()->route('admin.messages.index', $wasArchived ? ['archived' => 1] : []);
    }
}
