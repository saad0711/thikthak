@extends('layouts.app')

@section('title', 'Add a service')

@section('content')
    <div class="mx-auto max-w-2xl">
        <h1 class="page-title">Add a service</h1>

        <form method="POST" action="{{ route('admin.services.store') }}" class="card mt-6 space-y-5">
            @include('admin.services._form')
        </form>
    </div>
@endsection
