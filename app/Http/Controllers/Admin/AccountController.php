<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\TwoFactorResource;
use App\Rules\SafeEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

/** The signed-in admin's name, email, password and two-factor sign-in. */
class AccountController extends Controller
{
    public function edit(Request $request): Response
    {
        $user = $request->user();

        return Inertia::render('admin/Account', [
            'profile' => ['name' => $user->name, 'email' => $user->email],
            'twoFactor' => (new TwoFactorResource($user))->resolve($request),
            'meta' => ['title' => 'Account · Admin'],
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', ...SafeEmail::rules(), Rule::unique('users', 'email')->ignore($user->id)],
        ]);

        $user->forceFill($validated)->save();
        Inertia::flash('toast', 'Profile saved');

        return back();
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string', 'current_password:web'],
            'password' => ['required', 'confirmed', 'different:current_password', Password::min(12)->letters()->numbers()],
        ]);

        $user = $request->user();
        $user->forceFill(['password' => $validated['password']])->save();

        // Sign out every other browser that had this account open.
        Auth::guard('web')->logoutOtherDevices($validated['password']);
        $request->session()->regenerate();

        Inertia::flash('toast', 'Password changed');

        return back();
    }
}
