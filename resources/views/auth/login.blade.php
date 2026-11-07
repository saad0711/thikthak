@extends('layouts.app')

@section('title', 'Log in')

@section('content')
    <div class="mx-auto max-w-md">
        <h1 class="page-title">Log in to ThikThak</h1>
        <p class="mt-1 text-sm text-stone-600">New here? <a href="{{ route('register') }}" class="link">Create an account</a></p>

        <form method="POST" action="{{ route('login') }}" class="card mt-6 space-y-5">
            @csrf

            <x-input label="Email" name="email" type="email" autocomplete="email" required autofocus />
            <x-input label="Password" name="password" type="password" autocomplete="current-password" required />

            <div class="flex items-center justify-between text-sm">
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="remember" value="1" class="accent-teal-700" @checked(old('remember'))>
                    Remember me
                </label>
                <a href="{{ route('password.request') }}" class="link">Forgot password?</a>
            </div>

            <button type="submit" class="btn-primary w-full">Log in</button>
        </form>
    </div>
@endsection
