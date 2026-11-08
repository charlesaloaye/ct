<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/** Confirming (code) or turning off (current password) two-factor sign-in. */
class TwoFactorRequest extends FormRequest
{
    public function rules(): array
    {
        return $this->isMethod('delete')
            ? ['password' => ['required', 'string', 'current_password:web']]
            : ['code' => ['required', 'string', 'regex:/^\d{3}\s?\d{3}$/']];
    }
}
