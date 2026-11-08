<?php

namespace App\Http\Requests\Admin;

use App\Services\Ai\TextBlocks;
use Illuminate\Foundation\Http\FormRequest;

class AiSeoRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:200'],
            'excerpt' => ['nullable', 'string', 'max:1000'],
            // Plain text, or body blocks as saved on the article.
            'body' => ['nullable'],
        ];
    }

    public function bodyText(): string
    {
        $body = $this->input('body');

        return is_array($body) ? TextBlocks::toText($body) : (string) $body;
    }
}
