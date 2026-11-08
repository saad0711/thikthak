<?php

namespace Tests\Feature;

use App\Models\DayOff;
use App\Models\ProviderProfile;
use App\Models\Service;
use App\Models\User;
use App\Models\WorkingHour;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AvailabilityTest extends TestCase
{
    use RefreshDatabase;

    private User $providerUser;
    private ProviderProfile $providerProfile;
    private User $customerUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->providerUser = User::factory()->provider()->create();
        $this->providerProfile = $this->providerUser->providerProfile;
        $this->providerProfile->update(['slot_minutes' => 60]);

        $this->customerUser = User::factory()->customer()->create();
    }

    /**
     * Give the provider real F13 profile data worth a known completeness score.
     * Photo 15 + bio 15 + a service 25 + a service area 20.
     *
     * @param  array<int, string>  $parts
     */
    private function fillProfile(array $parts): void
    {
        $this->providerProfile->update(array_filter([
            'photo_path' => in_array('photo', $parts) ? 'profile-photos/me.jpg' : null,
            'bio' => in_array('bio', $parts) ? 'Experienced local technician.' : null,
            'area_name' => in_array('area', $parts) ? 'Mirpur' : null,
            'latitude' => in_array('area', $parts) ? 23.82 : null,
            'longitude' => in_array('area', $parts) ? 90.36 : null,
            'radius_km' => in_array('area', $parts) ? 10 : null,
        ]));

        if (in_array('service', $parts)) {
            $this->providerProfile->services()->attach(Service::factory()->create(), ['price' => 500]);
        }
    }

    /**
     * Security: Customer opening /provider/availability receives 403 Forbidden
     */
    public function test_customer_cannot_access_provider_availability(): void
    {
        $response = $this->actingAs($this->customerUser)->get(route('provider.availability.edit'));

        $response->assertStatus(403);
    }

    /**
     * Security: Provider B edits Provider A's availability -> 403 Forbidden
     */
    public function test_provider_b_cannot_edit_provider_a_availability(): void
    {
        $providerB = User::factory()->provider()->create();

        // Attempting to remove provider A's day off as provider B
        $dayOffA = DayOff::create([
            'provider_profile_id' => $this->providerProfile->id,
            'date' => now()->addDays(2)->toDateString(),
        ]);

        $response = $this->actingAs($providerB)->delete(route('provider.days-off.destroy', $dayOffA));

        $response->assertStatus(403);
    }

    /**
     * Provider can view their availability settings form.
     */
    public function test_provider_can_view_availability_edit_form(): void
    {
        $response = $this->actingAs($this->providerUser)->get(route('provider.availability.edit'));

        $response->assertStatus(200);
        $response->assertViewIs('provider.availability.edit');
        $response->assertSee('Availability Settings');
    }

    /**
     * Normal case: Provider saves valid working hours and slot length.
     */
    public function test_provider_can_update_working_hours_and_slot_length(): void
    {
        $response = $this->actingAs($this->providerUser)->put(route('provider.availability.update'), [
            'slot_minutes' => 90,
            'days' => [
                1 => ['enabled' => '1', 'start_time' => '09:00', 'end_time' => '17:00'], // Monday
                2 => ['enabled' => '1', 'start_time' => '10:00', 'end_time' => '18:00'], // Tuesday
                0 => ['enabled' => '0'], // Sunday closed
            ],
        ]);

        $response->assertRedirect(route('provider.availability.edit'));
        $response->assertSessionHas('success');

        $this->providerProfile->refresh();
        $this->assertEquals(90, $this->providerProfile->slot_minutes);

        $this->assertDatabaseHas('provider_working_hours', [
            'provider_profile_id' => $this->providerProfile->id,
            'day_of_week' => 1,
            'start_time' => '09:00',
            'end_time' => '17:00',
        ]);
        $this->assertDatabaseHas('provider_working_hours', [
            'provider_profile_id' => $this->providerProfile->id,
            'day_of_week' => 2,
            'start_time' => '10:00',
            'end_time' => '18:00',
        ]);
        $this->assertDatabaseMissing('provider_working_hours', [
            'provider_profile_id' => $this->providerProfile->id,
            'day_of_week' => 0,
        ]);
    }

    /**
     * Invalid case: End 09:00, start 17:00 -> Validation error
     */
    public function test_fails_when_end_time_is_before_or_equal_to_start_time(): void
    {
        $response = $this->actingAs($this->providerUser)->put(route('provider.availability.update'), [
            'slot_minutes' => 60,
            'days' => [
                1 => ['enabled' => '1', 'start_time' => '17:00', 'end_time' => '09:00'],
            ],
        ]);

        $response->assertSessionHasErrors('days.1.end_time');
    }

    /**
     * Boundary case: 30-minute block with 60-minute slots is rejected.
     */
    public function test_fails_when_working_block_is_shorter_than_slot_length(): void
    {
        $response = $this->actingAs($this->providerUser)->put(route('provider.availability.update'), [
            'slot_minutes' => 60,
            'days' => [
                1 => ['enabled' => '1', 'start_time' => '09:00', 'end_time' => '09:30'], // 30 min block < 60 min slot
            ],
        ]);

        $response->assertSessionHasErrors('days.1.end_time');

        // Boundary: exactly 60-minute block with 60-minute slot should be accepted
        $validResponse = $this->actingAs($this->providerUser)->put(route('provider.availability.update'), [
            'slot_minutes' => 60,
            'days' => [
                1 => ['enabled' => '1', 'start_time' => '09:00', 'end_time' => '10:00'],
            ],
        ]);

        $validResponse->assertSessionHasNoErrors();
    }

    /**
     * Invalid case: Slot length 45 -> Validation error
     */
    public function test_fails_when_slot_length_is_not_allowed(): void
    {
        $response = $this->actingAs($this->providerUser)->put(route('provider.availability.update'), [
            'slot_minutes' => 45,
            'days' => [
                1 => ['enabled' => '1', 'start_time' => '09:00', 'end_time' => '17:00'],
            ],
        ]);

        $response->assertSessionHasErrors('slot_minutes');
    }

    /**
     * Normal case: Provider can view days off index.
     */
    public function test_provider_can_view_days_off_page(): void
    {
        DayOff::create([
            'provider_profile_id' => $this->providerProfile->id,
            'date' => now()->addDays(5)->toDateString(),
            'reason' => 'Family event',
        ]);

        $response = $this->actingAs($this->providerUser)->get(route('provider.days-off.index'));

        $response->assertStatus(200);
        $response->assertViewIs('provider.days-off.index');
        $response->assertSee('Family event');
    }

    /**
     * Normal case: Provider adds a day off.
     */
    public function test_provider_can_add_day_off(): void
    {
        $futureDate = now()->addDays(5)->toDateString();

        $response = $this->actingAs($this->providerUser)->post(route('provider.days-off.store'), [
            'date' => $futureDate,
            'reason' => 'Eid-ul-Fitr',
        ]);

        $response->assertRedirect(route('provider.days-off.index'));
        $this->assertDatabaseHas('provider_days_off', [
            'provider_profile_id' => $this->providerProfile->id,
            'date' => $futureDate,
            'reason' => 'Eid-ul-Fitr',
        ]);
    }

    /**
     * Invalid case: Day off yesterday -> Validation error
     */
    public function test_fails_when_day_off_is_in_the_past(): void
    {
        $yesterday = now()->subDay()->toDateString();

        $response = $this->actingAs($this->providerUser)->post(route('provider.days-off.store'), [
            'date' => $yesterday,
            'reason' => 'Past day',
        ]);

        $response->assertSessionHasErrors('date');
    }

    /**
     * Invalid case: Same date twice -> Validation error
     */
    public function test_fails_when_adding_duplicate_day_off(): void
    {
        $futureDate = now()->addDays(3)->toDateString();

        DayOff::create([
            'provider_profile_id' => $this->providerProfile->id,
            'date' => $futureDate,
        ]);

        $response = $this->actingAs($this->providerUser)->post(route('provider.days-off.store'), [
            'date' => $futureDate,
            'reason' => 'Duplicate attempt',
        ]);

        $response->assertSessionHasErrors('date');
    }

    /**
     * Provider can remove an existing day off.
     */
    public function test_provider_can_remove_day_off(): void
    {
        $dayOff = DayOff::create([
            'provider_profile_id' => $this->providerProfile->id,
            'date' => now()->addDays(4)->toDateString(),
        ]);

        $response = $this->actingAs($this->providerUser)->delete(route('provider.days-off.destroy', $dayOff));

        $response->assertRedirect(route('provider.days-off.index'));
        $this->assertDatabaseMissing('provider_days_off', [
            'id' => $dayOff->id,
        ]);
    }

    /**
     * Business rule: Go online at 55% completeness -> Refused with a message
     */
    public function test_refuses_to_go_online_if_profile_is_incomplete(): void
    {
        // Set working hours so that requirement is satisfied
        WorkingHour::create([
            'provider_profile_id' => $this->providerProfile->id,
            'day_of_week' => 1,
            'start_time' => '09:00',
            'end_time' => '17:00',
        ]);

        // Incomplete profile (< 60%)
        $this->fillProfile(['photo', 'bio', 'service']);
        $this->assertSame(55, $this->providerProfile->completeness());

        $response = $this->actingAs($this->providerUser)->patch(route('provider.online-status.toggle'));

        $response->assertSessionHas('error');
        $this->providerProfile->refresh();
        $this->assertFalse($this->providerProfile->is_online);
    }

    /**
     * Business rule: Go online at 60%+ with no working hours -> Refused with a message
     */
    public function test_refuses_to_go_online_without_working_hours(): void
    {
        // Profile completeness is >= 60%, but no working hours exist
        $this->fillProfile(['bio', 'service', 'area']);
        $this->assertSame(60, $this->providerProfile->completeness());

        $this->assertFalse($this->providerProfile->hasWorkingHours());

        $response = $this->actingAs($this->providerUser)->patch(route('provider.online-status.toggle'));

        $response->assertSessionHas('error');
        $this->providerProfile->refresh();
        $this->assertFalse($this->providerProfile->is_online);
    }

    /**
     * Normal case: Provider goes online with complete profile and working hours.
     */
    public function test_provider_can_go_online_when_conditions_are_met(): void
    {
        WorkingHour::create([
            'provider_profile_id' => $this->providerProfile->id,
            'day_of_week' => 1,
            'start_time' => '09:00',
            'end_time' => '17:00',
        ]);

        // Exactly 60% is enough
        $this->fillProfile(['bio', 'service', 'area']);
        $this->assertSame(60, $this->providerProfile->completeness());

        $response = $this->actingAs($this->providerUser)->patch(route('provider.online-status.toggle'));

        $response->assertSessionHas('success');
        $this->providerProfile->refresh();
        $this->assertTrue($this->providerProfile->is_online);
    }

    /**
     * Business rule: a brand-new empty profile scores 0%, so it can't go online.
     */
    public function test_empty_profile_cannot_go_online(): void
    {
        WorkingHour::create([
            'provider_profile_id' => $this->providerProfile->id,
            'day_of_week' => 1,
            'start_time' => '09:00',
            'end_time' => '17:00',
        ]);

        $this->assertSame(0, $this->providerProfile->completeness());

        $this->actingAs($this->providerUser)->patch(route('provider.online-status.toggle'))
            ->assertSessionHas('error');

        $this->assertFalse($this->providerProfile->refresh()->is_online);
    }

    /**
     * Provider can toggle back offline anytime.
     */
    public function test_provider_can_toggle_offline(): void
    {
        $this->providerProfile->update(['is_online' => true]);

        $response = $this->actingAs($this->providerUser)->patch(route('provider.online-status.toggle'));

        $response->assertSessionHas('success');
        $this->providerProfile->refresh();
        $this->assertFalse($this->providerProfile->is_online);
    }

    /**
     * Provider can view week calendar.
     */
    public function test_provider_can_view_week_schedule_calendar(): void
    {
        WorkingHour::create([
            'provider_profile_id' => $this->providerProfile->id,
            'day_of_week' => 1,
            'start_time' => '09:00',
            'end_time' => '17:00',
        ]);

        $response = $this->actingAs($this->providerUser)->get(route('provider.schedule.week'));

        $response->assertStatus(200);
        $response->assertViewIs('provider.schedule.week');
        $response->assertSee('Weekly Schedule Calendar');
    }

    /**
     * An unusable ?date= falls back to the current week instead of crashing.
     */
    public function test_schedule_falls_back_to_this_week_for_an_invalid_date(): void
    {
        $this->actingAs($this->providerUser);

        $this->get('/provider/schedule?date=not-a-date')->assertOk();
        $this->get('/provider/schedule?date=2026-13-45')->assertOk();
        $this->get('/provider/schedule?date[]=2026-11-08')->assertOk();
    }

    /**
     * A valid ?date= still opens the week containing that date.
     */
    public function test_schedule_opens_the_week_of_a_valid_date(): void
    {
        $response = $this->actingAs($this->providerUser)->get('/provider/schedule?date=2026-11-18')->assertOk();

        $this->assertSame('2026-11-14', $response->viewData('startOfWeek')->toDateString());
    }
}
