<?php

namespace App\Services;

use App\Models\ProviderProfile;
use Carbon\Carbon;
use Carbon\CarbonImmutable;

class SlotGenerator
{
    /**
     * Generate available time slots for a provider on a given date.
     *
     * @param  ProviderProfile  $provider  The provider to generate slots for
     * @param  \DateTimeInterface|string  $date  The date to generate slots for
     * @param  array<array{start: string, end: string}>  $busyTimes  List of busy time ranges (each with 'start' and 'end' as H:i strings)
     * @return array<string>  Array of available slot start times as H:i strings
     */
    public function generate(ProviderProfile $provider, \DateTimeInterface|string $date, array $busyTimes = []): array
    {
        $date = $date instanceof \DateTimeInterface
            ? CarbonImmutable::instance($date)->startOfDay()
            : CarbonImmutable::parse($date)->startOfDay();

        // No slots on a closed day (no working hours set for this day of week)
        $dayOfWeek = (int) $date->format('w'); // 0 = Sunday, 6 = Saturday
        $workingHour = $provider->workingHoursForDay($dayOfWeek);

        if (! $workingHour) {
            return [];
        }

        // No slots on a day off
        if ($provider->isDayOff($date)) {
            return [];
        }

        $slotMinutes = $provider->slot_minutes;
        $startTime = CarbonImmutable::parse($date->format('Y-m-d') . ' ' . $workingHour->start_time);
        $endTime = CarbonImmutable::parse($date->format('Y-m-d') . ' ' . $workingHour->end_time);

        $now = Carbon::now();
        $isToday = $date->format('Y-m-d') === $now->format('Y-m-d');

        $slots = [];
        $current = $startTime;

        while ($current->copy()->addMinutes($slotMinutes)->lte($endTime)) {
            $slotEnd = $current->copy()->addMinutes($slotMinutes);

            // Skip slots that have already started today
            if ($isToday && $current->lte($now)) {
                $current = $current->addMinutes($slotMinutes);
                continue;
            }

            // Skip slots that overlap a busy time
            if ($this->overlapsBusyTime($current, $slotEnd, $busyTimes, $date)) {
                $current = $current->addMinutes($slotMinutes);
                continue;
            }

            $slots[] = $current->format('H:i');
            $current = $current->addMinutes($slotMinutes);
        }

        return $slots;
    }

    /**
     * Check if a slot overlaps any busy time range.
     *
     * A slot overlaps a busy range if the slot starts before the busy range ends
     * AND the slot ends after the busy range starts.
     */
    private function overlapsBusyTime(
        CarbonImmutable $slotStart,
        CarbonImmutable $slotEnd,
        array $busyTimes,
        CarbonImmutable $date
    ): bool {
        foreach ($busyTimes as $busy) {
            $busyStart = CarbonImmutable::parse($date->format('Y-m-d') . ' ' . $busy['start']);
            $busyEnd = CarbonImmutable::parse($date->format('Y-m-d') . ' ' . $busy['end']);

            // Overlap: slotStart < busyEnd AND slotEnd > busyStart
            if ($slotStart->lt($busyEnd) && $slotEnd->gt($busyStart)) {
                return true;
            }
        }

        return false;
    }
}
