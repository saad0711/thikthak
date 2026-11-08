@extends('layouts.app')

@section('title', 'Edit '.$service->name)

@section('content')
    <div class="mx-auto max-w-2xl">
        <h1 class="page-title">Edit "{{ $service->name }}"</h1>

        <form method="POST" action="{{ route('admin.services.update', $service) }}" class="card mt-6 space-y-5">
            @method('PUT')
            @include('admin.services._form')
        </form>
    </div>
@endsection
