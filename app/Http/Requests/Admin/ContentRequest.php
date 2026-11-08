<?php

namespace App\Http\Requests\Admin;

use App\Content\ContentType;
use App\Content\ContentTypes;
use App\Support\Status;
use Illuminate\Foundation\Http\FormRequest;

/** Create or update any admin content type; rules come from the type definition. */
class ContentRequest extends FormRequest
{
    public function type(): ContentType
    {
        return ContentTypes::for($this->route('type'));
    }

    protected function prepareForValidation(): void
    {
        // The admin UI shows "Published", "Featured"... accept either case.
        if ($this->has('status')) {
            $this->merge(['status' => Status::normalise($this->input('status'))]);
        }
    }

    public function rules(): array
    {
        return $this->type()->rules($this->isMethod('post'));
    }

    /** @return array<string, mixed> snake_case model attributes */
    public function attributesForModel(): array
    {
        return $this->type()->attributes($this->validated());
    }
}
