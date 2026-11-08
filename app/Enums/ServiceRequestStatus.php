<?php

namespace App\Enums;

/**
 * Lifecycle of a customer's service request (F4).
 *
 * Open → Quoted → Assigned → Completed, plus Cancelled and Expired.
 */
enum ServiceRequestStatus: string
{
    case Open = 'open';
    case Quoted = 'quoted';
    case Assigned = 'assigned';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case Expired = 'expired';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    /**
     * The statuses a request may move to from this one.
     *
     * @return array<int, self>
     */
    public function allowedNext(): array
    {
        return match ($this) {
            self::Open => [self::Quoted, self::Cancelled, self::Expired],
            self::Quoted => [self::Assigned, self::Cancelled, self::Expired],
            self::Assigned => [self::Completed],
            self::Completed, self::Cancelled, self::Expired => [],
        };
    }

    public function canMoveTo(self $next): bool
    {
        return in_array($next, $this->allowedNext(), true);
    }

    /**
     * Tailwind classes for the coloured status badge.
     */
    public function badgeClasses(): string
    {
        return match ($this) {
            self::Open => 'bg-sky-100 text-sky-800',
            self::Quoted => 'bg-amber-100 text-amber-800',
            self::Assigned => 'bg-violet-100 text-violet-800',
            self::Completed => 'bg-emerald-100 text-emerald-800',
            self::Cancelled => 'bg-stone-200 text-stone-700',
            self::Expired => 'bg-rose-100 text-rose-800',
        };
    }
}
