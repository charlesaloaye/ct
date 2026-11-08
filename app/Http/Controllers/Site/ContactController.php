<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Http\Requests\Site\ContactRequest;
use App\Mail\ContactReceived;
use App\Models\Message;
use App\Support\SiteSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ContactController extends Controller
{
    public const THANKS = "Thanks - your message is in. I'll reply within 24 hours.";

    public function __invoke(ContactRequest $request): RedirectResponse
    {
        $thanks = SiteSettings::value('sections.contact.success') ?: self::THANKS;

        // Bots get the same response as people, so they learn nothing.
        if (! $request->isSpam()) {
            $enquiry = Message::query()->create([
                'name' => $request->validated('name'),
                'email' => $request->validated('email'),
                'need' => $request->validated('need'),
                'body' => $request->validated('message'),
                'ip' => $request->ip(),
                'user_agent' => Str::limit((string) $request->userAgent(), 500, ''),
            ]);

            if ($recipient = config('site.contact_recipient') ?: SiteSettings::value('contact.email')) {
                Mail::to($recipient)->queue(new ContactReceived($enquiry));
            }
        }

        Inertia::flash('contact', $thanks);

        return back();
    }
}
