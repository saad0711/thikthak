@extends('layouts.app')

@section('title', 'Post a request')

@section('content')
    <div class="mx-auto max-w-3xl">
        <h1 class="page-title">Post a service request</h1>
        <p class="mt-1 text-sm text-stone-600">Describe the job once. Providers who offer this service will send you quotes.</p>

        <form method="POST" action="{{ route('requests.store') }}" enctype="multipart/form-data" class="card mt-6 space-y-5">
            @include('requests._form')

            <div class="flex gap-3">
                <button type="submit" class="btn-primary">Post request</button>
                <a href="{{ route('requests.index') }}" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection
