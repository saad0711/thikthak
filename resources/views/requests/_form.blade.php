{{-- Shared by "post a request" and "edit request" (F4). --}}
@csrf

<div>
    <label for="service_id" class="label">Service</label>
    <select id="service_id" name="service_id" class="input" required>
        <option value="">Choose a service</option>
        @foreach ($servicesByCategory as $categoryName => $services)
            <optgroup label="{{ $categoryName }}">
                @foreach ($services as $id => $name)
                    <option value="{{ $id }}" @selected((string) old('service_id', $serviceRequest->service_id) === (string) $id)>{{ $name }}</option>
                @endforeach
            </optgroup>
        @endforeach
    </select>
    @error('service_id')
        <p class="field-error">{{ $message }}</p>
    @enderror
</div>

<x-input label="Title" name="title" :value="$serviceRequest->title" maxlength="120" placeholder="e.g. Kitchen sink is leaking" required />
<x-textarea label="Describe the job" name="description" :value="$serviceRequest->description" rows="5" maxlength="2000"
            hint="At least 20 characters. Say what's wrong, what you need, and anything a provider should know." required />

<div class="grid gap-5 sm:grid-cols-2">
    <x-input label="Minimum budget (৳)" name="budget_min" type="number" min="1" step="1"
             :value="$serviceRequest->budget_min !== null ? (int) $serviceRequest->budget_min : null" required />
    <x-input label="Maximum budget (৳)" name="budget_max" type="number" min="1" step="1"
             :value="$serviceRequest->budget_max !== null ? (int) $serviceRequest->budget_max : null" required />
</div>

<div class="grid gap-5 sm:grid-cols-2">
    <x-input label="Needed by" name="deadline" type="date"
             :value="$serviceRequest->deadline?->toDateString()"
             :min="today()->addDay()->toDateString()"
             :max="today()->addDays(config('thikthak.requests.max_deadline_days'))->toDateString()"
             hint="Tomorrow at the earliest, up to {{ config('thikthak.requests.max_deadline_days') }} days ahead." required />
    <x-select label="Area" name="area_name" :options="array_combine($areas, $areas)" :value="$serviceRequest->area_name" placeholder="Choose your area" required />
</div>

@if ($serviceRequest->exists && $serviceRequest->photos->isNotEmpty())
    <fieldset>
        <legend class="label">Current photos</legend>
        <p class="hint mb-2">Tick a photo to remove it when you save.</p>
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-5">
            @foreach ($serviceRequest->photos as $photo)
                <label class="block cursor-pointer overflow-hidden rounded-lg border border-stone-200 has-checked:border-rose-400 has-checked:opacity-60">
                    <img src="{{ $photo->url() }}" alt="Photo {{ $loop->iteration }}" class="aspect-square w-full object-cover">
                    <span class="flex items-center gap-2 px-2 py-1.5 text-xs">
                        <input type="checkbox" name="remove_photos[]" value="{{ $photo->id }}" class="accent-rose-600"
                            @checked(in_array($photo->id, old('remove_photos', [])))>
                        Remove
                    </span>
                </label>
            @endforeach
        </div>
        @error('remove_photos.*')
            <p class="field-error">{{ $message }}</p>
        @enderror
    </fieldset>
@endif

<div>
    <label for="photos" class="label">{{ $serviceRequest->exists ? 'Add photos' : 'Photos (optional)' }}</label>
    <input id="photos" name="photos[]" type="file" multiple accept="image/jpeg,image/png,image/webp"
           class="block w-full text-sm text-stone-600 file:mr-3 file:rounded-lg file:border-0 file:bg-teal-50 file:px-3 file:py-2 file:text-sm file:font-medium file:text-teal-800 hover:file:bg-teal-100">
    <p class="hint">Up to {{ config('thikthak.requests.max_photos') }} photos in total. JPG, PNG or WEBP, 2 MB each.</p>
    @foreach (collect($errors->get('photos'))->merge(collect($errors->get('photos.*'))->flatten())->unique() as $message)
        <p class="field-error">{{ $message }}</p>
    @endforeach
</div>
