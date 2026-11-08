<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AiDraftRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => ['nullable', 'string', 'max:200'],
            'prompt' => ['required', 'string', 'min:10', 'max:4000'],
            'tone' => ['nullable', 'string', 'max:40'],
            'length' => ['nullable', 'string', 'max:40'],
        ];
    }
}
