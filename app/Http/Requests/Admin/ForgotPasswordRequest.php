<?php

namespace App\Http\Requests\Admin;

use App\Rules\SafeEmail;
use Illuminate\Foundation\Http\FormRequest;

class ForgotPasswordRequest extends FormRequest
{
    public function rules(): array
    {
        return ['email' => ['required', ...SafeEmail::rules()]];
    }
}
