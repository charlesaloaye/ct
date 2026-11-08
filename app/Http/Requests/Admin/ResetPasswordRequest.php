<?php

namespace App\Http\Requests\Admin;

use App\Rules\SafeEmail;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class ResetPasswordRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'token' => ['required', 'string'],
            'email' => ['required', ...SafeEmail::rules()],
            'password' => ['required', 'confirmed', Password::min(12)->letters()->numbers()],
        ];
    }
}
