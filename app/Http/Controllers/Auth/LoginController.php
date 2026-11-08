<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Log in. Repeated attempts are limited by the `throttle:login` route middleware.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->only('email', 'password');

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            // Same message whether the email or the password was wrong, so we don't reveal which.
            throw ValidationException::withMessages(['email' => __('auth.failed')]);
        }

        if (! $request->user()->is_active) {
            Auth::logout();

            throw ValidationException::withMessages(['email' => 'This account has been deactivated.']);
        }

        $request->session()->regenerate();

        return redirect()->intended(route($request->user()->dashboardRoute()));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
