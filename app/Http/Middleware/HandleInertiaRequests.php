<?php

namespace App\Http\Middleware;

use App\Http\Resources\Admin\UserResource;
use App\Support\AdminCounts;
use App\Support\SiteShell;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function share(Request $request): array
    {
        $isAdminArea = $request->is('admin', 'admin/*');
        $admin = $request->user()?->is_admin ? $request->user() : null;

        return [
            ...parent::share($request),
            'site' => fn () => SiteShell::get(),
            'auth' => fn () => ['user' => $admin ? (new UserResource($admin))->resolve($request) : null],
            // Sidebar badges, refreshed on every admin visit.
            'adminCounts' => fn () => $isAdminArea && $admin ? AdminCounts::get() : null,
            'appUrl' => rtrim(config('app.url'), '/'),
        ];
    }
}
