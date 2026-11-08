<?php

namespace Tests\Feature\ServiceRequests;

use App\Enums\ServiceRequestStatus;
use App\Exceptions\InvalidStatusTransition;
use App\Models\ServiceRequest;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StatusLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_request_moves_through_the_full_lifecycle(): void
    {
        $serviceRequest = ServiceRequest::factory()->create();

        $serviceRequest->transitionTo(ServiceRequestStatus::Quoted);
        $serviceRequest->transitionTo(ServiceRequestStatus::Assigned);
        $serviceRequest->transitionTo(ServiceRequestStatus::Completed);

        $this->assertSame(ServiceRequestStatus::Completed, $serviceRequest->fresh()->status);
    }

    public function test_expired_request_cannot_be_reopened(): void
    {
        $serviceRequest = ServiceRequest::factory()->withStatus(ServiceRequestStatus::Expired)->create();

        $this->expectException(InvalidStatusTransition::class);

        $serviceRequest->transitionTo(ServiceRequestStatus::Open);
    }

    public function test_completed_request_cannot_be_cancelled(): void
    {
        $serviceRequest = ServiceRequest::factory()->withStatus(ServiceRequestStatus::Completed)->create();

        $this->expectException(InvalidStatusTransition::class);

        $serviceRequest->cancel();
    }

    public function test_only_open_requests_are_editable(): void
    {
        foreach (ServiceRequestStatus::cases() as $status) {
            $serviceRequest = ServiceRequest::factory()->withStatus($status)->make();

            $this->assertSame($status === ServiceRequestStatus::Open, $serviceRequest->isEditable(), $status->value);
        }
    }

    public function test_expire_command_closes_overdue_open_and_quoted_requests_only(): void
    {
        $overdueOpen = ServiceRequest::factory()->overdue()->create();
        $overdueQuoted = ServiceRequest::factory()->overdue()->withStatus(ServiceRequestStatus::Quoted)->create();
        $overdueAssigned = ServiceRequest::factory()->overdue()->withStatus(ServiceRequestStatus::Assigned)->create();
        $nextWeek = ServiceRequest::factory()->create(['deadline' => today()->addWeek()]);
        $dueToday = ServiceRequest::factory()->create(['deadline' => today()]);

        $this->artisan('requests:expire')->expectsOutput('Expired 2 service request(s).')->assertSuccessful();

        $this->assertSame(ServiceRequestStatus::Expired, $overdueOpen->fresh()->status);
        $this->assertSame(ServiceRequestStatus::Expired, $overdueQuoted->fresh()->status);
        $this->assertSame(ServiceRequestStatus::Assigned, $overdueAssigned->fresh()->status);
        $this->assertSame(ServiceRequestStatus::Open, $nextWeek->fresh()->status);
        $this->assertSame(ServiceRequestStatus::Open, $dueToday->fresh()->status);
    }

    public function test_expire_command_is_scheduled_hourly(): void
    {
        $event = collect(app(Schedule::class)->events())
            ->first(fn (Event $event) => str_contains((string) $event->command, 'requests:expire'));

        $this->assertNotNull($event);
        $this->assertSame('0 * * * *', $event->expression);
    }
}
