<?php

namespace Tests\Unit;

use App\Models\DayOff;
use App\Models\ProviderProfile;
use App\Models\User;
use App\Models\WorkingHour;
use App\Services\SlotGenerator;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SlotGeneratorTest extends TestCase
{
    use RefreshDatabase;

    private SlotGenerator $generator;
    private ProviderProfile $provider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->generator = new SlotGenerator();

        $user = User::factory()->provider()->create();
        $this->provider = $user->providerProfile;
        $this->provider->update(['slot_minutes' => 60]);
    }

    /**
     * Normal case: Monday 09:00–17:00, 60-minute slots -> 8 slots, first 09:00, last 16:00
     */
    public function test_generates_eight_60_minute_slots_for_standard_day(): void
    {
        // Monday is day 1
        WorkingHour::create([
            'provider_profile_id' => $this->provider->id,
            'day_of_week' => 1,
            'start_time' => '09:00',
            'end_time' => '17:00',
        ]);

        // A known Monday in the future
        $monday = '2026-11-16';

        $slots = $this->generator->generate($this->provider, $monday);

        $this->assertCount(8, $slots);
        $this->assertEquals('09:00', $slots[0]);
        $this->assertEquals('16:00', end($slots));
        $this->assertEquals([
            '09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00',
        ], $slots);
    }

    /**
     * Normal case: Monday 09:00–17:00, 90-minute slots -> 5 slots, last 15:00
     */
    public function test_generates_five_90_minute_slots(): void
    {
        $this->provider->update(['slot_minutes' => 90]);

        WorkingHour::create([
            'provider_profile_id' => $this->provider->id,
            'day_of_week' => 1,
            'start_time' => '09:00',
            'end_time' => '17:00',
        ]);

        $monday = '2026-11-16';

        $slots = $this->generator->generate($this->provider, $monday);

        // 09:00-10:30, 10:30-12:00, 12:00-13:30, 13:30-15:00, 15:00-16:30
        $this->assertCount(5, $slots);
        $this->assertEquals('09:00', $slots[0]);
        $this->assertEquals('15:00', end($slots));
        $this->assertEquals([
            '09:00', '10:30', '12:00', '13:30', '15:00',
        ], $slots);
    }

    /**
     * Business rule: Date is a day off -> 0 slots
     */
    public function test_returns_no_slots_on_a_day_off(): void
    {
        WorkingHour::create([
            'provider_profile_id' => $this->provider->id,
            'day_of_week' => 1,
            'start_time' => '09:00',
            'end_time' => '17:00',
        ]);

        $monday = '2026-11-16';

        DayOff::create([
            'provider_profile_id' => $this->provider->id,
            'date' => $monday,
            'reason' => 'Holiday',
        ]);

        $slots = $this->generator->generate($this->provider, $monday);

        $this->assertEmpty($slots);
    }

    /**
     * Business rule: Day marked closed -> 0 slots
     */
    public function test_returns_no_slots_on_a_closed_day(): void
    {
        // No working hours created for Sunday (day 0)
        $sunday = '2026-11-15';

        $slots = $this->generator->generate($this->provider, $sunday);

        $this->assertEmpty($slots);
    }

    /**
     * Business rule: Asking for today at 11:30 -> No slots before 11:30
     */
    public function test_does_not_return_slots_that_have_already_started_today(): void
    {
        WorkingHour::create([
            'provider_profile_id' => $this->provider->id,
            'day_of_week' => 1, // Monday
            'start_time' => '09:00',
            'end_time' => '17:00',
        ]);

        // Freeze time at Monday 11:30
        $mondayAt1130 = Carbon::parse('2026-11-16 11:30:00');
        $this->travelTo($mondayAt1130);

        $slots = $this->generator->generate($this->provider, '2026-11-16');

        $this->assertNotContains('09:00', $slots);
        $this->assertNotContains('10:00', $slots);
        $this->assertNotContains('11:00', $slots);
        $this->assertContains('12:00', $slots);
        $this->assertEquals(['12:00', '13:00', '14:00', '15:00', '16:00'], $slots);
    }

    /**
     * Business rule: Busy time 10:00–11:00 -> The 10:00 slot is left out
     */
    public function test_excludes_slots_that_overlap_with_busy_times(): void
    {
        WorkingHour::create([
            'provider_profile_id' => $this->provider->id,
            'day_of_week' => 1,
            'start_time' => '09:00',
            'end_time' => '17:00',
        ]);

        $monday = '2026-11-16';

        $busyTimes = [
            ['start' => '10:00', 'end' => '11:00'],
        ];

        $slots = $this->generator->generate($this->provider, $monday, $busyTimes);

        $this->assertNotContains('10:00', $slots);
        $this->assertContains('09:00', $slots);
        $this->assertContains('11:00', $slots);
        $this->assertCount(7, $slots);
    }

    /**
     * Partial busy time overlap: busy from 10:15 to 10:45 should block the 10:00–11:00 slot.
     */
    public function test_excludes_slot_with_partial_busy_overlap(): void
    {
        WorkingHour::create([
            'provider_profile_id' => $this->provider->id,
            'day_of_week' => 1,
            'start_time' => '09:00',
            'end_time' => '17:00',
        ]);

        $monday = '2026-11-16';

        $busyTimes = [
            ['start' => '10:15', 'end' => '10:45'],
        ];

        $slots = $this->generator->generate($this->provider, $monday, $busyTimes);

        $this->assertNotContains('10:00', $slots);
        $this->assertContains('09:00', $slots);
        $this->assertContains('11:00', $slots);
    }
}
