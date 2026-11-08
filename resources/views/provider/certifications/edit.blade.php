@extends('layouts.app')

@section('title', 'Edit certification - ThikThak')

@section('content')
<div class="max-w-xl mx-auto py-8 px-4 space-y-6">

    <h1 class="text-2xl font-bold">Edit certification</h1>

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

    <form method="POST" action="{{ route('provider.certifications.update', $certification) }}" enctype="multipart/form-data"
          class="bg-white p-5 rounded shadow space-y-3">
        @csrf
        @method('PUT')

        <div>
            <label for="title" class="block text-sm font-medium">Title</label>
            <input type="text" id="title" name="title" maxlength="100"
                   value="{{ old('title', $certification->title) }}" class="border rounded p-2 w-full">
        </div>
        <div>
            <label for="issuer" class="block text-sm font-medium">Issued by</label>
            <input type="text" id="issuer" name="issuer" maxlength="100"
                   value="{{ old('issuer', $certification->issuer) }}" class="border rounded p-2 w-full">
        </div>
        <div>
            <label for="year" class="block text-sm font-medium">Year (1970 to {{ date('Y') }})</label>
            <input type="number" id="year" name="year" min="1970" max="{{ date('Y') }}"
                   value="{{ old('year', $certification->year) }}" class="border rounded p-2 w-32">
        </div>
        <div>
            @if ($certification->file_path)
                <p class="text-sm mb-1">
                    Current file:
                    <a href="{{ route('provider.certifications.file', $certification) }}" target="_blank" class="text-blue-600 hover:underline">View file</a>
                </p>
            @endif
            <label for="file" class="block text-sm font-medium">
                {{ $certification->file_path ? 'Replace the file (optional)' : 'Certificate file (optional)' }}
                : JPG, PNG, WebP or PDF, up to 2 MB
            </label>
            <input type="file" id="file" name="file" accept=".jpg,.jpeg,.png,.webp,.pdf">
        </div>

        <div class="flex items-center gap-4">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Save changes</button>
            <a href="{{ route('provider.profile.edit') }}#certifications" class="text-gray-600 hover:underline">Cancel</a>
        </div>
    </form>

</div>
@endsection
