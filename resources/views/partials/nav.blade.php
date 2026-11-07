@php
    $navLink = fn (string $pattern) => request()->routeIs($pattern)
        ? 'font-semibold text-teal-700'
        : 'text-stone-600 hover:text-stone-900';
@endphp

<header class="border-b border-stone-200 bg-white">
    <nav class="mx-auto flex max-w-6xl flex-wrap items-center gap-x-8 gap-y-3 px-4 py-3">
        <a href="{{ route('home') }}" class="text-xl font-semibold tracking-tight text-teal-700">ThikThak</a>

        <div class="flex flex-1 flex-wrap items-center gap-x-5 gap-y-1 text-sm">
            <a href="{{ route('catalog.search') }}" class="{{ $navLink('catalog.*') }}">Services</a>

            @auth
                @if (auth()->user()->isCustomer())
                    <a href="{{ route('customer.dashboard') }}" class="{{ $navLink('customer.dashboard') }}">Dashboard</a>
                    <a href="{{ route('requests.index') }}" class="{{ $navLink('requests.index') }}">My requests</a>
                    <a href="{{ route('requests.create') }}" class="{{ $navLink('requests.create') }}">Post a request</a>
                @elseif (auth()->user()->isProvider())
                    <a href="{{ route('provider.dashboard') }}" class="{{ $navLink('provider.*') }}">Dashboard</a>
                @elseif (auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="{{ $navLink('admin.dashboard') }}">Dashboard</a>
                    <a href="{{ route('admin.categories.index') }}" class="{{ $navLink('admin.categories.*') }}">Categories</a>
                    <a href="{{ route('admin.services.index') }}" class="{{ $navLink('admin.services.*') }}">Services list</a>
                @endif
            @endauth
        </div>

        <div class="flex items-center gap-3 text-sm">
            @auth
                <a href="{{ route('account.edit') }}" class="flex items-center gap-2 {{ $navLink('account.*') }}">
                    @if (auth()->user()->avatarUrl())
                        <img src="{{ auth()->user()->avatarUrl() }}" alt="" class="size-8 rounded-full object-cover">
                    @else
                        <span class="flex size-8 items-center justify-center rounded-full bg-teal-100 text-xs font-semibold text-teal-800">{{ auth()->user()->initials() }}</span>
                    @endif
                    <span class="hidden sm:inline">{{ auth()->user()->name }}</span>
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-stone-600 hover:text-stone-900">Log out</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="{{ $navLink('login') }}">Log in</a>
                <a href="{{ route('register') }}" class="btn-primary">Sign up</a>
            @endauth
        </div>
    </nav>
</header>
