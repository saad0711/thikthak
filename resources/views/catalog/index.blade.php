@extends('layouts.app')

@section('title', 'Find trusted local services')

@section('content')
    <section class="rounded-2xl bg-teal-700 px-6 py-10 text-white sm:px-10">
        <h1 class="max-w-2xl text-3xl font-semibold tracking-tight sm:text-4xl">Find trusted electricians, plumbers, tutors and more near you</h1>
        <p class="mt-3 max-w-2xl text-teal-50">Compare clear price ranges, post your job, and get quotes from local professionals across Dhaka.</p>

        <form method="GET" action="{{ route('catalog.search') }}" class="mt-6 flex max-w-xl flex-col gap-3 sm:flex-row">
            <label for="hero-q" class="sr-only">Search services</label>
            <input id="hero-q" type="search" name="q" placeholder="What do you need done?" maxlength="100"
                   class="w-full rounded-lg border-0 bg-white px-4 py-3 text-stone-900 placeholder:text-stone-400 outline-none focus:ring-2 focus:ring-teal-300">
            <button type="submit" class="rounded-lg bg-white px-5 py-3 font-medium text-teal-800 hover:bg-teal-50">Search</button>
        </form>
    </section>

    <section class="mt-10">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <h2 class="text-xl font-semibold">Browse by category</h2>
            <a href="{{ route('catalog.search') }}" class="link text-sm">See all services</a>
        </div>

        <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @forelse ($categories as $category)
                <a href="{{ route('catalog.category', $category) }}" class="card flex flex-col gap-2 p-5 hover:border-teal-300">
                    <span class="text-3xl" aria-hidden="true">{{ $category->icon }}</span>
                    <span class="font-semibold text-stone-900">{{ $category->name }}</span>
                    <span class="text-sm text-stone-500">{{ trans_choice(':count service|:count services', $category->services_count) }}</span>
                </a>
            @empty
                <p class="text-sm text-stone-600">The catalog is empty right now. Check back soon.</p>
            @endforelse
        </div>
    </section>
@endsection
