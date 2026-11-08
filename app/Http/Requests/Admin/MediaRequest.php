<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class MediaRequest extends FormRequest
{
    public const IMAGE_MAX_KB = 5120;

    public const CV_MAX_KB = 10240;

    public function rules(): array
    {
        $isCv = $this->isCv();

        return [
            'alt' => ['nullable', 'string', 'max:255'],
            // SVG is excluded on purpose: it can carry scripts.
            'file' => $isCv
                ? ['required', 'file', 'mimes:pdf', 'mimetypes:application/pdf', 'max:'.self::CV_MAX_KB]
                : ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:'.self::IMAGE_MAX_KB],
        ];
    }

    /** The CV endpoint takes a PDF; every other upload is an image. */
    public function isCv(): bool
    {
        return $this->routeIs('admin.cv.store');
    }
}
