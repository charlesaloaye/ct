<?php

namespace App\Http\Requests\Site;

use App\Rules\SafeEmail;
use Illuminate\Foundation\Http\FormRequest;

class SubscribeRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'email' => ['required', ...SafeEmail::rules()],
            'website' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function email(): string
    {
        return mb_strtolower(trim($this->validated('email')));
    }
}
