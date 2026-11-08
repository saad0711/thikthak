@extends('layouts.app')

@section('title', 'Availability Settings')

@section('content')
<div class="mx-auto max-w-4xl">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="page-title">Availability Settings</h1>
            <p class="mt-1 text-sm text-stone-600">Configure your weekly working hours, booking slot duration, and instant availability.</p>
        </div>

        {{-- Online/Offline Toggle --}}
        <form method="POST" action="{{ route('provider.online-status.toggle') }}">
            @csrf
            @method('PATCH')
            <button type="submit"
                class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium transition cursor-pointer {{ $profile->is_online ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200 ring-1 ring-emerald-300' : 'bg-stone-100 text-stone-700 hover:bg-stone-200 ring-1 ring-stone-300' }}">
                <span class="inline-block size-2 rounded-full {{ $profile->is_online ? 'bg-emerald-600' : 'bg-stone-400' }}"></span>
                {{ $profile->is_online ? 'Online for jobs' : 'Offline' }}
            </button>
        </form>
    </div>

    @if (session('success'))
        <div class="mt-4 rounded-lg bg-emerald-50 p-4 text-sm text-emerald-800 border border-emerald-200">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mt-4 rounded-lg bg-rose-50 p-4 text-sm text-rose-800 border border-rose-200">
            {{ session('error') }}
        </div>
    @endif

    <form method="POST" action="{{ route('provider.availability.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('PUT')

        {{-- Slot Length Card --}}
        <div class="card">
            <h2 class="text-base font-semibold text-stone-900">Booking Slot Duration</h2>
            <p class="hint">The duration of each booking window when customers schedule a service.</p>

            <div class="mt-3 max-w-xs">
                <select name="slot_minutes" id="slot_minutes" class="input">
                    @foreach([30, 60, 90, 120] as $minutes)
                        <option value="{{ $minutes }}" {{ old('slot_minutes', $profile->slot_minutes) == $minutes ? 'selected' : '' }}>
                            {{ $minutes }} minutes
                        </option>
                    @endforeach
                </select>
                @error('slot_minutes')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Weekly Working Hours Card --}}
        <div class="card">
            <h2 class="text-base font-semibold text-stone-900">Weekly Working Hours</h2>
            <p class="hint">Specify the start and end time for each day you are available. Uncheck any day you are closed.</p>

            @php
                $dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
            @endphp

            <div class="mt-4 divide-y divide-stone-100">
                @foreach($dayNames as $index => $dayName)
                    @php
                        $wh = $workingHours->get($index);
                        $isEnabled = old("days.{$index}.enabled", $wh ? '1' : '0');
                        $startTime = old("days.{$index}.start_time", $wh?->start_time ? \Carbon\Carbon::parse($wh->start_time)->format('H:i') : '09:00');
                        $endTime = old("days.{$index}.end_time", $wh?->end_time ? \Carbon\Carbon::parse($wh->end_time)->format('H:i') : '17:00');
                    @endphp
                    <div class="flex flex-col py-3 sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div class="w-36">
                            <label class="inline-flex items-center gap-2 cursor-pointer">
                                <input type="hidden" name="days[{{ $index }}][enabled]" value="0">
                                <input type="checkbox"
                                    name="days[{{ $index }}][enabled]"
                                    value="1"
                                    {{ $isEnabled ? 'checked' : '' }}
                                    class="size-4 rounded border-stone-300 text-teal-700 focus:ring-teal-600"
                                    onchange="toggleDay(this, {{ $index }})">
                                <span class="text-sm font-medium text-stone-800">{{ $dayName }}</span>
                            </label>
                        </div>

                        <div class="flex items-center gap-2" id="day-times-{{ $index }}" style="{{ $isEnabled ? '' : 'opacity: 0.4;' }}">
                            <input type="time"
                                name="days[{{ $index }}][start_time]"
                                value="{{ $startTime }}"
                                class="rounded-lg border border-stone-300 bg-white px-2 py-1 text-sm shadow-xs outline-none focus:border-teal-600 focus:ring-1 focus:ring-teal-600"
                                {{ $isEnabled ? '' : 'disabled' }}
                                id="start-{{ $index }}">
                            <span class="text-xs text-stone-500">to</span>
                            <input type="time"
                                name="days[{{ $index }}][end_time]"
                                value="{{ $endTime }}"
                                class="rounded-lg border border-stone-300 bg-white px-2 py-1 text-sm shadow-xs outline-none focus:border-teal-600 focus:ring-1 focus:ring-teal-600"
                                {{ $isEnabled ? '' : 'disabled' }}
                                id="end-{{ $index }}">
                        </div>

                        <div class="sm:flex-1 text-right">
                            @error("days.{$index}.end_time")
                                <p class="field-error text-xs">{{ $message }}</p>
                            @enderror
                            @error("days.{$index}.start_time")
                                <p class="field-error text-xs">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-6 flex items-center justify-between border-t border-stone-100 pt-4">
                <a href="{{ route('provider.schedule.week') }}" class="link text-sm">
                    &larr; View Week Calendar
                </a>
                <button type="submit" class="btn-primary">
                    Save Changes
                </button>
            </div>
        </div>
    </form>
</div>

<script>
    function toggleDay(checkbox, dayIndex) {
        const timesDiv = document.getElementById('day-times-' + dayIndex);
        const startInput = document.getElementById('start-' + dayIndex);
        const endInput = document.getElementById('end-' + dayIndex);

        if (checkbox.checked) {
            timesDiv.style.opacity = '1';
            startInput.disabled = false;
            endInput.disabled = false;
        } else {
            timesDiv.style.opacity = '0.4';
            startInput.disabled = true;
            endInput.disabled = true;
        }
    }
</script>
@endsection
