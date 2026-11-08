<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Http\Requests\Site\SubscribeRequest;
use App\Mail\ConfirmSubscription;
use App\Models\Subscriber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Inertia\Inertia;

class SubscribeController extends Controller
{
    public const CHECK_INBOX = 'Almost there - check your inbox to confirm.';

    public function store(SubscribeRequest $request): RedirectResponse
    {
        // Bots (honeypot filled) get the same answer and nothing is stored.
        if (blank($request->input('website'))) {
            $subscriber = Subscriber::query()->firstOrCreate(['email' => $request->email()]);

            // Same answer whether or not the address is already on the list.
            if ($subscriber->confirmed_at === null) {
                if (blank($subscriber->confirm_token)) {
                    $subscriber->forceFill(['confirm_token' => Str::random(48)])->save();
                }
                Mail::to($subscriber->email)->queue(new ConfirmSubscription($subscriber));
            }
        }

        Inertia::flash('subscribe', self::CHECK_INBOX);

        return back();
    }

    public function confirm(string $token): RedirectResponse
    {
        $subscriber = Subscriber::query()->where('confirm_token', $token)->first();
        $subscriber?->forceFill(['confirmed_at' => now(), 'confirm_token' => null])->save();

        return redirect()->route('blog.index', ['subscription' => $subscriber ? 'confirmed' : 'invalid']);
    }

    public function unsubscribe(string $token): RedirectResponse
    {
        $deleted = Subscriber::query()->where('unsubscribe_token', $token)->delete();

        return redirect()->route('blog.index', ['subscription' => $deleted ? 'removed' : 'invalid']);
    }
}
