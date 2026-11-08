<?php

namespace App\Http\Requests\Admin;

use App\Support\Status;
use Illuminate\Foundation\Http\FormRequest;

class IndexRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $status = Status::normalise($this->input('status'));
        $this->merge(['status' => $status === 'all' ? null : $status]);
    }

    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'string', 'max:20'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:200'],
        ];
    }

    public function perPage(): int
    {
        return (int) $this->input('per_page', 50);
    }
}
