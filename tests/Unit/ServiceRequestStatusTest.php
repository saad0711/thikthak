<?php

namespace Tests\Unit;

use App\Enums\ServiceRequestStatus;
use PHPUnit\Framework\TestCase;

class ServiceRequestStatusTest extends TestCase
{
    public function test_open_can_move_to_quoted_cancelled_or_expired(): void
    {
        $this->assertTrue(ServiceRequestStatus::Open->canMoveTo(ServiceRequestStatus::Quoted));
        $this->assertTrue(ServiceRequestStatus::Open->canMoveTo(ServiceRequestStatus::Cancelled));
        $this->assertTrue(ServiceRequestStatus::Open->canMoveTo(ServiceRequestStatus::Expired));
        $this->assertFalse(ServiceRequestStatus::Open->canMoveTo(ServiceRequestStatus::Completed));
    }

    public function test_assigned_can_only_be_completed(): void
    {
        $this->assertSame([ServiceRequestStatus::Completed], ServiceRequestStatus::Assigned->allowedNext());
    }

    public function test_finished_statuses_are_final(): void
    {
        foreach ([ServiceRequestStatus::Completed, ServiceRequestStatus::Cancelled, ServiceRequestStatus::Expired] as $status) {
            $this->assertSame([], $status->allowedNext(), $status->value);
        }
    }
}
