<?php

namespace App\Models\Concerns;

use App\Models\DayOff;
use App\Models\WorkingHour;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasAvailability
{
    public function workingHours(): HasMany
    {
        return $this->hasMany(WorkingHour::class, 'provider_profile_id');
    }

    public function daysOff(): HasMany
    {
        return $this->hasMany(DayOff::class, 'provider_profile_id');
    }

    /**
     * Get working hours for a specific day of the week (0=Sunday, 6=Saturday).
     */
    public function workingHoursForDay(int $dayOfWeek): ?WorkingHour
    {
        return $this->workingHours()->where('day_of_week', $dayOfWeek)->first();
    }

    /**
     * Check if a specific date is a day off.
     */
    public function isDayOff(\DateTimeInterface $date): bool
    {
        return $this->daysOff()->whereDate('date', $date)->exists();
    }

    /**
     * Check if the provider has at least one day with working hours set.
     */
    public function hasWorkingHours(): bool
    {
        return $this->workingHours()->exists();
    }

    /**
     * Determine if the provider is allowed to toggle online.
     * Requires canGoOnline() (60%+ profile completeness) AND at least one working day.
     */
    public function canToggleOnline(): bool
    {
        return $this->canGoOnline() && $this->hasWorkingHours();
    }
}
