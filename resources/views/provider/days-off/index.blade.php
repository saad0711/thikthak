@extends('layouts.app')

@section('title', 'Manage Days Off')

@section('content')
<div class="mx-auto max-w-4xl">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="page-title">Days Off &amp; Holidays</h1>
            <p class="mt-1 text-sm text-stone-600">Schedule dates when you will not be taking bookings (e.g. festivals, vacations, personal days).</p>
        </div>

        <a href="{{ route('provider.schedule.week') }}" class="btn-secondary text-sm">
            View Schedule Calendar &rarr;
        </a>
    </div>

    @if (session('success'))
        <div class="mt-4 rounded-lg bg-emerald-50 p-4 text-sm text-emerald-800 border border-emerald-200">
            {{ session('success') }}
        </div>
    @endif

    {{-- Add Day Off Form Card --}}
    <div class="card mt-6">
        <h2 class="text-base font-semibold text-stone-900">Schedule a New Day Off</h2>
        <form method="POST" action="{{ route('provider.days-off.store') }}" class="mt-4 flex flex-col gap-4 sm:flex-row sm:items-end">
            @csrf
            <div class="flex-1">
                <label for="date" class="label">Date</label>
                <input type="date"
                    name="date"
                    id="date"
                    value="{{ old('date') }}"
                    min="{{ now()->toDateString() }}"
                    required
                    class="input">
                @error('date')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex-2">
                <label for="reason" class="label">Reason (optional)</label>
                <input type="text"
                    name="reason"
                    id="reason"
                    value="{{ old('reason') }}"
                    placeholder="e.g. Eid holiday, family vacation"
                    maxlength="255"
                    class="input">
                @error('reason')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <button type="submit" class="btn-primary whitespace-nowrap">
                    Add Day Off
                </button>
            </div>
        </form>
    </div>

    {{-- Days Off List Card --}}
    <div class="card mt-6">
        <h2 class="text-base font-semibold text-stone-900">Upcoming Days Off</h2>

        @if($daysOff->isEmpty())
            <p class="mt-4 text-sm text-stone-500 text-center py-6">No upcoming days off scheduled. You are available according to your weekly working hours.</p>
        @else
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-stone-200 text-left text-sm">
                    <thead>
                        <tr class="text-xs uppercase tracking-wider text-stone-500">
                            <th class="py-3 px-4 font-semibold">Date</th>
                            <th class="py-3 px-4 font-semibold">Day of Week</th>
                            <th class="py-3 px-4 font-semibold">Reason</th>
                            <th class="py-3 px-4 font-semibold text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @foreach($daysOff as $dayOff)
                            <tr>
                                <td class="py-3 px-4 font-medium text-stone-900">{{ $dayOff->date->format('M d, Y') }}</td>
                                <td class="py-3 px-4 text-stone-600">{{ $dayOff->date->format('l') }}</td>
                                <td class="py-3 px-4 text-stone-600">{{ $dayOff->reason ?? '—' }}</td>
                                <td class="py-3 px-4 text-right">
                                    <form method="POST" action="{{ route('provider.days-off.destroy', $dayOff) }}" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="text-rose-600 hover:text-rose-800 text-xs font-semibold cursor-pointer"
                                            onclick="return confirm('Remove this day off?')">
                                            Remove
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
