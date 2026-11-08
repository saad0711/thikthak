@extends('layouts.app')

@section('title', 'Weekly Schedule Calendar')

@section('content')
<div class="mx-auto max-w-6xl">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="page-title">Weekly Schedule Calendar</h1>
            <p class="mt-1 text-sm text-stone-600">Visual week schedule (Saturday to Friday) with generated booking slots.</p>
        </div>

        <div class="flex items-center gap-3">
            <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-medium {{ $profile->is_online ? 'bg-emerald-100 text-emerald-800' : 'bg-stone-100 text-stone-600' }}">
                <span class="size-1.5 rounded-full {{ $profile->is_online ? 'bg-emerald-600' : 'bg-stone-400' }}"></span>
                {{ $profile->is_online ? 'Online' : 'Offline' }}
            </span>
            <span class="text-xs text-stone-500">
                Slot duration: <strong class="text-stone-800">{{ $profile->slot_minutes }} min</strong>
            </span>
            <a href="{{ route('provider.availability.edit') }}" class="btn-secondary text-xs">
                Edit availability
            </a>
            <a href="{{ route('provider.days-off.index') }}" class="btn-secondary text-xs">
                Days off
            </a>
        </div>
    </div>

    {{-- Week navigation --}}
    <div class="card mt-6">
        <div class="flex items-center justify-between border-b border-stone-200 pb-4">
            <a href="{{ route('provider.schedule.week', ['date' => $prevWeek]) }}" class="btn-secondary text-xs">
                &larr; Previous Week
            </a>

            <div class="text-center">
                <h2 class="text-base font-semibold text-stone-900">
                    {{ $startOfWeek->format('M d') }} &ndash; {{ $endOfWeek->format('M d, Y') }}
                </h2>
                <p class="text-xs text-stone-500">Saturday to Friday</p>
            </div>

            <a href="{{ route('provider.schedule.week', ['date' => $nextWeek]) }}" class="btn-secondary text-xs">
                Next Week &rarr;
            </a>
        </div>

        {{-- Week grid: 7 columns (Saturday to Friday) --}}
        <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-7 gap-3">
            @foreach($weekDays as $day)
                <div class="flex flex-col rounded-xl border p-3 min-h-[220px] {{ $day['is_today'] ? 'border-teal-500 bg-teal-50/30' : 'border-stone-200 bg-white' }}">
                    <div class="border-b border-stone-100 pb-2 text-center">
                        <span class="text-xs font-semibold uppercase tracking-wider {{ $day['is_today'] ? 'text-teal-700' : 'text-stone-500' }}">
                            {{ $day['day_short'] }}
                        </span>
                        <div class="text-sm font-bold {{ $day['is_today'] ? 'text-teal-800' : 'text-stone-900' }}">
                            {{ $day['date_formatted'] }}
                        </div>
                        @if ($day['is_today'])
                            <span class="inline-block rounded bg-teal-700 px-1.5 py-0.5 text-[10px] font-medium text-white">Today</span>
                        @endif
                    </div>

                    <div class="mt-2 flex-1">
                        @if($day['is_day_off'])
                            <div class="rounded-lg bg-rose-50 p-2 text-center text-xs font-medium text-rose-700 border border-rose-200">
                                Day Off
                                @if($day['day_off_reason'])
                                    <p class="mt-1 text-[11px] font-normal text-rose-600">{{ $day['day_off_reason'] }}</p>
                                @endif
                            </div>
                        @elseif($day['working_hour'])
                            <div class="mb-2 rounded bg-stone-100 px-2 py-1 text-center text-xs font-medium text-stone-700">
                                {{ \Carbon\Carbon::parse($day['working_hour']->start_time)->format('g:i A') }}
                                &ndash;
                                {{ \Carbon\Carbon::parse($day['working_hour']->end_time)->format('g:i A') }}
                            </div>

                            @if(count($day['slots']) > 0)
                                <div class="space-y-1 max-h-60 overflow-y-auto pr-0.5">
                                    @foreach($day['slots'] as $slot)
                                        <div class="rounded border border-teal-200 bg-teal-50/70 px-2 py-1 text-center text-xs font-medium text-teal-800">
                                            {{ \Carbon\Carbon::parse($slot)->format('g:i A') }}
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="mt-4 text-center text-xs text-stone-400">No free slots</p>
                            @endif
                        @else
                            <div class="mt-4 rounded bg-stone-50 p-2 text-center text-xs text-stone-400">
                                Closed
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
