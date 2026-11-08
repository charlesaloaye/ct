<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AiDraftRequest;
use App\Http\Requests\Admin\AiRewriteRequest;
use App\Http\Requests\Admin\AiSeoRequest;
use App\Http\Resources\Admin\AiResultResource;
use App\Services\Ai\AiException;
use App\Services\Ai\AiWriter;
use Closure;
use Illuminate\Http\JsonResponse;

/** The article editor's writing assistant. The provider key stays on the server. */
class AiController extends Controller
{
    public function __construct(private readonly AiWriter $writer)
    {
    }

    public function draft(AiDraftRequest $request): AiResultResource|JsonResponse
    {
        return $this->run(fn () => ['body' => $this->writer->draft(
            (string) $request->validated('title'),
            $request->validated('prompt'),
            $request->validated('tone') ?: 'Practical',
            $request->validated('length') ?: 'Medium',
        )]);
    }

    public function rewrite(AiRewriteRequest $request): AiResultResource|JsonResponse
    {
        return $this->run(fn () => ['text' => $this->writer->rewrite(
            $request->validated('text'),
            $request->validated('action'),
            (string) $request->validated('context'),
        )]);
    }

    public function seo(AiSeoRequest $request): AiResultResource|JsonResponse
    {
        return $this->run(fn () => $this->writer->seo(
            $request->validated('title'),
            (string) $request->validated('excerpt'),
            $request->bodyText(),
        ));
    }

    private function run(Closure $call): AiResultResource|JsonResponse
    {
        try {
            return new AiResultResource($call());
        } catch (AiException $e) {
            return response()->json(['message' => $e->getMessage()], $e->status);
        }
    }
}
