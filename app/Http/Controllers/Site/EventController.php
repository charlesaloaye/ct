<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Http\Requests\Site\EventRequest;
use App\Models\Event;
use Illuminate\Http\Response;

class EventController extends Controller
{
    public function __invoke(EventRequest $request): Response
    {
        Event::record($request->validated('type'), $request->pagePath(), $request->meta());

        return response()->noContent();
    }
}
