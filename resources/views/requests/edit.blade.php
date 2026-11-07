@extends('layouts.app')

@section('title', 'Edit request')

@section('content')
    <div class="mx-auto max-w-3xl">
        <h1 class="page-title">Edit your request</h1>
        <p class="mt-1 text-sm text-stone-600">You can edit a request until the first provider sends a quote.</p>

        <form method="POST" action="{{ route('requests.update', $serviceRequest) }}" enctype="multipart/form-data" class="card mt-6 space-y-5">
            @method('PUT')
            @include('requests._form')

            <div class="flex gap-3">
                <button type="submit" class="btn-primary">Save changes</button>
                <a href="{{ route('requests.show', $serviceRequest) }}" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection
