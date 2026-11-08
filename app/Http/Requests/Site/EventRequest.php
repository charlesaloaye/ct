<?php

namespace App\Http\Requests\Site;

use App\Models\Event;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EventRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(Event::TYPES)],
            'path' => ['nullable', 'string', 'max:255', 'starts_with:/'],
            'href' => ['nullable', 'required_if:type,outbound_click', 'url:http,https', 'max:500'],
            'label' => ['nullable', 'string', 'max:120'],
        ];
    }

    /** Only the page path, plus the link target for outbound clicks. Never IPs or user agents. */
    public function meta(): ?array
    {
        $meta = array_filter([
            'href' => $this->validated('href'),
            'label' => $this->validated('label'),
        ]);

        return $meta ?: null;
    }

    public function pagePath(): ?string
    {
        // Drop query strings and fragments, which can carry personal data.
        $path = $this->validated('path');

        return $path ? strtok($path, '?#') : null;
    }
}
