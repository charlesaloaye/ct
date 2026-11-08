<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LoginRequest;
use App\Models\User;
use App\Support\TwoFactor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class AuthController extends Controller
{
    /** How long a correct password waits for its authenticator code. */
    private const CHALLENGE_MINUTES = 5;

    public function create(): Response
    {
        return Inertia::render('admin/auth/Login', ['meta' => ['title' => 'Sign in']]);
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $guard = Auth::guard('web');

        // Check the password without signing in, so a second factor can be asked for first.
        if (! $guard->validate($request->safe()->only(['email', 'password']))) {
            throw ValidationException::withMessages(['email' => 'Those details do not match an admin account.']);
        }

        /** @var User $user */
        $user = $guard->getLastAttempted();

        if (! $user->is_admin) {
            throw ValidationException::withMessages(['email' => 'Those details do not match an admin account.']);
        }

        if ($user->hasTwoFactorEnabled()) {
            $request->session()->put('login', [
                'id' => $user->getKey(),
                'remember' => $request->boolean('remember'),
                'expires' => now()->addMinutes(self::CHALLENGE_MINUTES)->getTimestamp(),
            ]);

            return redirect()->route('admin.two-factor.challenge');
        }

        return $this->signIn($request, $user, $request->boolean('remember'));
    }

    public function challenge(Request $request): Response|RedirectResponse
    {
        if (! $this->pendingUser($request)) {
            return redirect()->route('admin.login');
        }

        return Inertia::render('admin/auth/TwoFactorChallenge', ['meta' => ['title' => 'Two-factor sign-in']]);
    }

    public function verify(Request $request): RedirectResponse
    {
        $request->validate(['code' => ['required', 'string', 'max:32']]);
        $user = $this->pendingUser($request);

        if (! $user) {
            Inertia::flash('toast', 'That sign-in took too long. Enter your password again.');

            return redirect()->route('admin.login');
        }

        if (! TwoFactor::passes($user, $request->input('code'))) {
            throw ValidationException::withMessages(['code' => 'That code is not valid. Try the latest one from your app, or a recovery code.']);
        }

        $remember = (bool) $request->session()->pull('login')['remember'];

        return $this->signIn($request, $user, $remember);
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }

    private function signIn(Request $request, User $user, bool $remember): RedirectResponse
    {
        Auth::guard('web')->login($user, $remember);
        $request->session()->forget('login');
        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'));
    }

    private function pendingUser(Request $request): ?User
    {
        $pending = $request->session()->get('login');

        if (! is_array($pending) || ($pending['expires'] ?? 0) < now()->getTimestamp()) {
            $request->session()->forget('login');

            return null;
        }

        $user = User::query()->find($pending['id']);

        return $user?->is_admin && $user->hasTwoFactorEnabled() ? $user : null;
    }
}
