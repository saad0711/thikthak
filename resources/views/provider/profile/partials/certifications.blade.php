{{-- Certifications: list (edit / delete) and an "add" form. --}}
<section id="certifications" class="bg-white p-5 rounded shadow">
    <h2 class="text-lg font-semibold mb-3">Certifications</h2>

    @if ($profile->certifications->isEmpty())
        <p class="text-gray-600 mb-4">You have not added any certifications yet.</p>
    @else
        <ul class="divide-y mb-4">
            @foreach ($profile->certifications as $certification)
                <li class="py-2 flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <p class="font-medium">{{ $certification->title }}</p>
                        <p class="text-sm text-gray-600">{{ $certification->issuer }}, {{ $certification->year }}</p>
                        @if ($certification->file_path)
                            <a href="{{ route('provider.certifications.file', $certification) }}" target="_blank" class="text-sm text-blue-600 hover:underline">View file</a>
                        @endif
                    </div>
                    <div class="flex items-center gap-3">
                        <a href="{{ route('provider.certifications.edit', $certification) }}" class="text-blue-600 hover:underline">Edit</a>
                        <form method="POST" action="{{ route('provider.certifications.destroy', $certification) }}"
                              onsubmit="return confirm('Delete this certification?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:underline">Delete</button>
                        </form>
                    </div>
                </li>
            @endforeach
        </ul>
    @endif

    <h3 class="font-medium mb-2">Add a certification</h3>
    <form method="POST" action="{{ route('provider.certifications.store') }}" enctype="multipart/form-data" class="space-y-3">
        @csrf

        <div>
            <label for="cert_title" class="block text-sm font-medium">Title</label>
            <input type="text" id="cert_title" name="title" maxlength="100" value="{{ old('title') }}" class="border rounded p-2 w-full">
        </div>
        <div>
            <label for="cert_issuer" class="block text-sm font-medium">Issued by</label>
            <input type="text" id="cert_issuer" name="issuer" maxlength="100" value="{{ old('issuer') }}" class="border rounded p-2 w-full">
        </div>
        <div>
            <label for="cert_year" class="block text-sm font-medium">Year (1970 to {{ date('Y') }})</label>
            <input type="number" id="cert_year" name="year" min="1970" max="{{ date('Y') }}" value="{{ old('year') }}" class="border rounded p-2 w-32">
        </div>
        <div>
            <label for="cert_file" class="block text-sm font-medium">Certificate file (optional: JPG, PNG, WebP or PDF, up to 2 MB)</label>
            <input type="file" id="cert_file" name="file" accept=".jpg,.jpeg,.png,.webp,.pdf">
        </div>

        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Add certification</button>
    </form>
</section>
