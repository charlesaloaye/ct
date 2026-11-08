<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MediaRequest;
use App\Http\Resources\Admin\MediaResource;
use App\Models\Media;
use App\Support\MediaUrl;
use App\Support\SiteSettings;
use Illuminate\Http\JsonResponse;

/** Uploads from the editors (JSON, so the editor can place the image before saving). */
class MediaController extends Controller
{
    public function store(MediaRequest $request): JsonResponse
    {
        return (new MediaResource($this->save($request, 'media/'.now()->format('Y/m'))))->response()->setStatusCode(201);
    }

    /** Replaces the CV behind "Download CV" straight away. */
    public function cv(MediaRequest $request): JsonResponse
    {
        $media = $this->save($request, 'cv');
        SiteSettings::save('contact', ['cvPath' => $media->path]);

        return (new MediaResource($media))->response()->setStatusCode(201);
    }

    private function save(MediaRequest $request, string $folder): Media
    {
        $file = $request->file('file');
        $disk = MediaUrl::disk();
        $path = $file->store($folder, ['disk' => $disk, 'visibility' => 'public']);

        return Media::query()->create([
            'path' => $path,
            'disk' => $disk,
            'mime' => $file->getMimeType(),
            'size' => $file->getSize(),
            'alt' => $request->validated('alt'),
            'uploaded_by' => $request->user()->id,
        ]);
    }
}
