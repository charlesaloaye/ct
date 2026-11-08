<?php

namespace App\Http\Requests\Admin;

use App\Content\ContentTypes;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReorderRequest extends FormRequest
{
    public function rules(): array
    {
        $table = (new (ContentTypes::for($this->route('type'))->model()))->getTable();

        return [
            'ids' => ['required', 'array', 'min:1', 'max:500'],
            'ids.*' => ['integer', 'distinct', Rule::exists($table, 'id')],
        ];
    }
}
