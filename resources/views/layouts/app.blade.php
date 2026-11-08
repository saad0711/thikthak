<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@hasSection('title')@yield('title') · @endif{{ config('app.name') }}</title>

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="flex min-h-screen flex-col bg-stone-50 text-stone-800 antialiased">
    @include('partials.nav')

    <main class="mx-auto w-full max-w-6xl flex-1 px-4 py-8">
        <x-flash />

        @yield('content')
    </main>

    <footer class="border-t border-stone-200 bg-white">
        <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-2 px-4 py-6 text-sm text-stone-500">
            <span><span class="font-semibold text-teal-700">ThikThak</span> · Trusted local services in Bangladesh</span>
            <span>CSE 470 project</span>
        </div>
    </footer>
</body>
</html>
