@extends('layouts.app')

@section('title', 'Create an account')

@section('content')
    <div class="mx-auto max-w-lg">
        <h1 class="page-title">Create your ThikThak account</h1>
        <p class="mt-1 text-sm text-stone-600">Already have one? <a href="{{ route('login') }}" class="link">Log in</a></p>

        <form method="POST" action="{{ route('register') }}" class="card mt-6 space-y-5">
            @csrf

            <fieldset>
                <legend class="label">I want to</legend>
                <div class="grid gap-3 sm:grid-cols-2">
                    @foreach ($roles as $role)
                        <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-stone-300 p-3 has-checked:border-teal-600 has-checked:bg-teal-50">
                            <input type="radio" name="role" value="{{ $role->value }}" class="mt-1 accent-teal-700"
                                @checked(old('role', 'customer') === $role->value)>
                            <span>
                                <span class="block text-sm font-medium">{{ $role->signupPitch() }}</span>
                                <span class="block text-xs text-stone-500">{{ $role->label() }}</span>
                            </span>
                        </label>
                    @endforeach
                </div>
                @error('role')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </fieldset>

            <x-input label="Full name" name="name" autocomplete="name" required />
            <x-input label="Email" name="email" type="email" autocomplete="email" required />
            <x-input label="Mobile number" name="phone" type="tel" placeholder="01712345678" hint="Bangladeshi mobile number, 11 digits." required />
            <x-select label="Area (optional)" name="area" :options="array_combine($areas, $areas)" placeholder="Choose your area" />
            <x-input label="Password" name="password" type="password" autocomplete="new-password" hint="At least 8 characters." required />
            <x-input label="Confirm password" name="password_confirmation" type="password" autocomplete="new-password" required />

            <button type="submit" class="btn-primary w-full">Create account</button>
        </form>
    </div>
@endsection
