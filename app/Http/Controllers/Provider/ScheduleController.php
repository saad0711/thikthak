<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Services\SlotGenerator;
use Carbon\CarbonImmutable;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    public function __construct(
        private readonly SlotGenerator $slotGenerator,
    ) {}

    /**
     * Display the week calendar (Saturday to Friday).
     */
    public function week(Request $request): View
    {
        $profile = auth()->user()->providerProfile;
        Gate::authorize('view', $profile);

        // Determine the week to show, default to current week
        $requestedDate = $request->query('date')
            ? CarbonImmutable::parse($request->query('date'))
            : CarbonImmutable::now();

        // Find the Saturday that starts this week (Saturday = 6)
        $startOfWeek = $requestedDate->is('Saturday')
            ? $requestedDate
            : $requestedDate->previous('Saturday');

        $endOfWeek = $startOfWeek->addDays(6); // Friday

        // Build the week data
        $weekDays = [];
        $period = CarbonPeriod::create($startOfWeek, $endOfWeek);

        $workingHours = $profile->workingHours->keyBy('day_of_week');
        $daysOff = $profile->daysOff()
            ->whereBetween('date', [$startOfWeek->toDateString(), $endOfWeek->toDateString()])
            ->get()
            ->keyBy(fn ($d) => $d->date->toDateString());

        foreach ($period as $day) {
            $dayOfWeek = (int) $day->format('w');
            $dateStr = $day->toDateString();

            $wh = $workingHours->get($dayOfWeek);
            $isDayOff = $daysOff->has($dateStr);
            $dayOffReason = $isDayOff ? $daysOff->get($dateStr)->reason : null;

            $slots = [];
            if ($wh && ! $isDayOff) {
                $slots = $this->slotGenerator->generate($profile, $day, []);
            }

            $weekDays[] = [
                'date' => $day,
                'day_name' => $day->format('l'),
                'day_short' => $day->format('D'),
                'date_formatted' => $day->format('M d'),
                'is_today' => $day->isToday(),
                'working_hour' => $wh,
                'is_day_off' => $isDayOff,
                'day_off_reason' => $dayOffReason,
                'slots' => $slots,
            ];
        }

        return view('provider.schedule.week', [
            'profile' => $profile,
            'weekDays' => $weekDays,
            'startOfWeek' => $startOfWeek,
            'endOfWeek' => $endOfWeek,
            'prevWeek' => $startOfWeek->subWeek()->toDateString(),
            'nextWeek' => $startOfWeek->addWeek()->toDateString(),
        ]);
    }
}
