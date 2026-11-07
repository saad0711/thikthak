<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function create(): View
    {
        return view('auth.register', [
            'areas' => array_keys(config('thikthak.areas')),
            'roles' => UserRole::registrable(),
        ]);
    }

    /**
     * Create a customer or provider account. Providers also get an empty profile row,
     * which F13, F14 and F17 build on.
     */
    public function store(RegisterRequest $request): RedirectResponse
    {
        $user = DB::transaction(function () use ($request) {
            $user = new User($request->safe()->only(['name', 'email', 'phone', 'password', 'area']));
            $user->role = UserRole::from($request->validated('role'));
            $user->save();

            if ($user->isProvider()) {
                $user->providerProfile()->create();
            }

            return $user;
        });

        // Sends the "verify your email" message (written to storage/logs/laravel.log locally).
        event(new Registered($user));

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route($user->dashboardRoute());
    }
}
