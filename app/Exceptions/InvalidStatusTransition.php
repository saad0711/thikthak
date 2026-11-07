<?php

namespace App\Exceptions;

use App\Enums\ServiceRequestStatus;
use DomainException;

/**
 * Thrown when code tries to move a service request into a status its current status doesn't allow.
 */
class InvalidStatusTransition extends DomainException
{
    public static function between(ServiceRequestStatus $from, ServiceRequestStatus $to): self
    {
        return new self("A {$from->value} request can't become {$to->value}.");
    }
}
