<?php

namespace App\Http\Requests\Site;

use App\Support\SiteSettings;
use App\Rules\SafeEmail;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ContactRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', ...SafeEmail::rules()],
            'need' => $this->needs() === [] ? ['nullable', 'string', 'max:60'] : ['required', 'string', Rule::in($this->needs())],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
            // Honeypot: hidden from people, filled in by bots.
            'website' => ['nullable', 'string', 'max:255'],
        ];
    }

    /** The "What do you need?" options set in Admin → Pages & SEO. */
    public function needs(): array
    {
        return array_values(array_filter((array) SiteSettings::value('sections.contact.needs', [])));
    }

    public function isSpam(): bool
    {
        return filled($this->input('website'));
    }
}
