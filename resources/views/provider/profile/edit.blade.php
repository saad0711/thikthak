@extends('layouts.app')

@section('title', 'My Profile - ThikThak')

@section('content')
<div class="max-w-3xl mx-auto py-8 px-4 space-y-6">

    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold">My Profile</h1>
        <a href="{{ route('providers.show', $profile) }}" class="text-blue-600 hover:underline">View my public page</a>
    </div>

    {{-- Success message --}}
    @if (session('success'))
        <div class="bg-green-100 border border-green-300 text-green-800 p-3 rounded">
            {{ session('success') }}
        </div>
    @endif

    {{-- Validation errors (the same message is shown only once) --}}
    @if ($errors->any())
        <div class="bg-red-100 border border-red-300 text-red-800 p-3 rounded">
            <p class="font-semibold">Please fix these problems:</p>
            <ul class="list-disc ml-5">
                @foreach (collect($errors->all())->unique() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @include('provider.profile.partials.completeness')
    @include('provider.profile.partials.basic')
    @include('provider.profile.partials.services')
    @include('provider.profile.partials.area')
    @include('provider.profile.partials.certifications')
    @include('provider.profile.partials.portfolio')

</div>
@endsection
