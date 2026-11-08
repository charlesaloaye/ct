<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AiRewriteRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'text' => ['required', 'string', 'max:8000'],
            'action' => ['required', Rule::in(['continue', 'clearer', 'example', 'shorten'])],
            'context' => ['nullable', 'string', 'max:60000'],
        ];
    }
}
