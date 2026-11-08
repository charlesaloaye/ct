<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ForgotPasswordRequest;
use App\Http\Requests\Admin\ResetPasswordRequest;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class PasswordResetController extends Controller
{
    public const SENT = 'If that address belongs to an admin account, a reset link is on its way.';

    public function create(): Response
    {
        return Inertia::render('admin/auth/ForgotPassword', ['meta' => ['title' => 'Reset your password']]);
    }

    /** Emails a reset link. Same answer whether or not the account exists. */
    public function send(ForgotPasswordRequest $request): RedirectResponse
    {
        $isAdmin = User::query()->where('email', $request->validated('email'))->where('is_admin', true)->exists();

        if ($isAdmin) {
            Password::broker()->sendResetLink(['email' => $request->validated('email')]);
        }

        Inertia::flash('status', self::SENT);

        return back();
    }

    public function edit(Request $request, string $token): Response
    {
        return Inertia::render('admin/auth/ResetPassword', [
            'token' => $token,
            'email' => (string) $request->query('email', ''),
            'meta' => ['title' => 'Choose a new password'],
        ]);
    }

    public function update(ResetPasswordRequest $request): RedirectResponse
    {
        $status = Password::broker()->reset(
            $request->safe()->only(['email', 'password', 'password_confirmation', 'token']),
            function (User $user, string $password) {
                $user->forceFill(['password' => $password, 'remember_token' => Str::random(60)])->save();
                event(new PasswordReset($user));
            },
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages(['email' => 'This reset link is invalid or has expired. Ask for a new one.']);
        }

        Inertia::flash('toast', 'Password changed. You can sign in now.');

        return redirect()->route('admin.login');
    }
}
