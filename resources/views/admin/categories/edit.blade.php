@extends('layouts.app')

@section('title', 'Edit '.$category->name)

@section('content')
    <div class="mx-auto max-w-2xl">
        <h1 class="page-title">Edit "{{ $category->name }}"</h1>

        <form method="POST" action="{{ route('admin.categories.update', $category) }}" class="card mt-6 space-y-5">
            @method('PUT')
            @include('admin.categories._form')
        </form>
    </div>
@endsection
