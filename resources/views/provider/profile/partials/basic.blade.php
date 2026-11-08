{{-- Basic information: photo, years of experience, bio. --}}
<section id="basic" class="bg-white p-5 rounded shadow">
    <h2 class="text-lg font-semibold mb-3">Basic information</h2>

    <form method="POST" action="{{ route('provider.profile.update') }}" enctype="multipart/form-data" class="space-y-4">
        @csrf
        @method('PUT')

        <div>
            @if ($profile->photo_path)
                <img src="{{ $profile->photoUrl() }}" alt="Your profile photo" class="w-24 h-24 rounded-full object-cover mb-2">
            @endif
            <label for="photo" class="block text-sm font-medium">Profile photo (JPG, PNG or WebP, up to 2 MB)</label>
            <input type="file" id="photo" name="photo" accept=".jpg,.jpeg,.png,.webp">
        </div>

        <div>
            <label for="years_experience" class="block text-sm font-medium">Years of experience (0 to 60)</label>
            <input type="number" id="years_experience" name="years_experience" min="0" max="60"
                   value="{{ old('years_experience', $profile->years_experience) }}"
                   class="border rounded p-2 w-32">
        </div>

        <div>
            <label for="bio" class="block text-sm font-medium">About you (up to 1000 characters)</label>
            <textarea id="bio" name="bio" rows="5" maxlength="1000"
                      class="border rounded p-2 w-full">{{ old('bio', $profile->bio) }}</textarea>
        </div>

        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Save basic information</button>
    </form>
</section>
