<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * F14 demo data: working hours, slot length and days off for the demo provider.
 */
class AvailabilitySeeder extends Seeder
{
    public function run(): void
    {
        $provider = User::where('email', 'provider@thikthak.test')->first();

        if (! $provider || ! $provider->providerProfile) {
            return;
        }

        $profile = $provider->providerProfile;
        $profile->update([
            'slot_minutes' => 60,
            'is_online' => true,
        ]);

        // Default: Saturday to Thursday 09:00 - 17:00 (Friday closed)
        // 0 = Sunday, 1 = Monday, 2 = Tuesday, 3 = Wednesday, 4 = Thursday, 5 = Friday, 6 = Saturday
        foreach ([0, 1, 2, 3, 4, 6] as $dayOfWeek) {
            $profile->workingHours()->firstOrCreate(
                ['day_of_week' => $dayOfWeek],
                ['start_time' => '09:00', 'end_time' => '17:00']
            );
        }

        // Add an upcoming day off
        $profile->daysOff()->firstOrCreate(
            ['date' => today()->addDays(7)->toDateString()],
            ['reason' => 'National Holiday']
        );
    }
}
