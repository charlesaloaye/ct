<?php

use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
            SecurityHeaders::class,
        ]);
        $middleware->redirectGuestsTo(fn () => route('admin.login'));
        $middleware->redirectUsersTo(fn () => route('admin.dashboard'));
        $middleware->alias(['admin' => EnsureUserIsAdmin::class]);
        // Check the admin flag before route model binding, so non-admins get 403, never 404.
        $middleware->prependToPriorityList(before: SubstituteBindings::class, prepend: EnsureUserIsAdmin::class);
        // Analytics beacons carry no session data; unsubscribe links are one-click from email clients.
        $middleware->validateCsrfTokens(except: ['events', 'unsubscribe/*']);
        // Behind Nginx / a load balancer, trust X-Forwarded-* so rate limits see the real IP.
        $middleware->trustProxies(at: env('TRUSTED_PROXIES', '127.0.0.1'));
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->shouldRenderJsonWhen(fn (Request $request) => $request->expectsJson() && ! $request->header('X-Inertia'));

        // Error pages use the site's own design (Inertia "Error" page) instead of Laravel's default views.
        $exceptions->respond(function (Response $response, Throwable $e, Request $request) {
            $status = $response->getStatusCode();
            $handled = [403, 404, 419, 429, 500, 503];

            if (! in_array($status, $handled, true) || ($request->expectsJson() && ! $request->header('X-Inertia'))) {
                return $response;
            }
            // Keep the detailed debug page for server errors while developing.
            if ($status >= 500 && config('app.debug')) {
                return $response;
            }
            // An expired form (CSRF token) sends people back with a note instead of a dead end.
            if ($status === 419 && $request->hasSession()) {
                Inertia::flash('toast', 'This page had been open a while. Please try that again.');

                return back();
            }

            // A rate-limited form goes back to where it was sent from, with a note.
            if ($status === 429 && ! $request->isMethod('GET') && $request->hasSession()) {
                Inertia::flash('toast', 'Too many attempts. Please wait a little and try again.');

                return back();
            }

            // Unmatched URLs never ran the web middleware, so add the shared site data here.
            Inertia::share(app(HandleInertiaRequests::class)->share($request));

            return Inertia::render('Error', ['status' => $status])
                ->toResponse($request)
                ->setStatusCode($status);
        });
    })->create();
