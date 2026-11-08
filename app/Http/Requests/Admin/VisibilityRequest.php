<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class VisibilityRequest extends FormRequest
{
    public function rules(): array
    {
        // Omit "hidden" to toggle.
        return ['hidden' => ['sometimes', 'boolean']];
    }
}
