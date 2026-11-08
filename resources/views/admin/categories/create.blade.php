@extends('layouts.app')

@section('title', 'Add a category')

@section('content')
    <div class="mx-auto max-w-2xl">
        <h1 class="page-title">Add a category</h1>

        <form method="POST" action="{{ route('admin.categories.store') }}" class="card mt-6 space-y-5">
            @include('admin.categories._form')
        </form>
    </div>
@endsection
