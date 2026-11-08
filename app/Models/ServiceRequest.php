<?php

namespace App\Models;

use App\Enums\ServiceRequestStatus;
use App\Exceptions\InvalidStatusTransition;
use App\Support\Money;
use Database\Factories\ServiceRequestFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * F4: a job a customer posts so providers can quote on it.
 *
 * The status rules live here, in transitionTo(), so the controller, the expiry
 * command and later features (F5 quoting, F15 tracking) can't put a request
 * into an impossible state.
 */
#[Fillable(['service_id', 'title', 'description', 'budget_min', 'budget_max', 'deadline', 'area_name'])]
class ServiceRequest extends Model
{
    /** @use HasFactory<ServiceRequestFactory> */
    use HasFactory;

    /**
     * New requests start as Open.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => 'open',
    ];

    protected function casts(): array
    {
        return [
            'budget_min' => 'decimal:2',
            'budget_max' => 'decimal:2',
            'deadline' => 'date',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'status' => ServiceRequestStatus::class,
            'cancelled_at' => 'datetime',
        ];
    }

    /**
     * Whenever the area changes, store that area's coordinates for distance-based features.
     */
    protected static function booted(): void
    {
        static::saving(function (self $request) {
            if ($request->isDirty('area_name')) {
                [$request->latitude, $request->longitude] = config("thikthak.areas.{$request->area_name}", [null, null]);
            }
        });
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    /**
     * @return BelongsTo<Service, $this>
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Photos in upload order. (Order is applied when loading, not here, so that
     * count() and max() on this relation stay valid SQL on MariaDB.)
     *
     * @return HasMany<ServiceRequestPhoto, $this>
     */
    public function photos(): HasMany
    {
        return $this->hasMany(ServiceRequestPhoto::class);
    }

    /**
     * Business rule: details can only change until the first quote arrives.
     */
    public function isEditable(): bool
    {
        return $this->status === ServiceRequestStatus::Open;
    }

    /**
     * Business rule: a request can be cancelled while it's Open or Quoted.
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, [ServiceRequestStatus::Open, ServiceRequestStatus::Quoted], true);
    }

    /**
     * Move the request to a new status, or throw if that move isn't allowed.
     */
    public function transitionTo(ServiceRequestStatus $next): void
    {
        if (! $this->status->canMoveTo($next)) {
            throw InvalidStatusTransition::between($this->status, $next);
        }

        $this->status = $next;
        $this->save();
    }

    public function cancel(?string $reason = null): void
    {
        if (! $this->canBeCancelled()) {
            throw InvalidStatusTransition::between($this->status, ServiceRequestStatus::Cancelled);
        }

        $this->cancelled_at = now();
        $this->cancel_reason = filled($reason) ? $reason : null;
        $this->transitionTo(ServiceRequestStatus::Cancelled);
    }

    public function budgetLabel(): string
    {
        return Money::range($this->budget_min, $this->budget_max);
    }

    #[Scope]
    protected function withStatus(Builder $query, ?ServiceRequestStatus $status): void
    {
        $query->when($status, fn (Builder $q) => $q->where('status', $status->value));
    }

    /**
     * Open or Quoted requests whose deadline day has already passed.
     */
    #[Scope]
    protected function overdue(Builder $query): void
    {
        $query->whereIn('status', [ServiceRequestStatus::Open->value, ServiceRequestStatus::Quoted->value])
            ->whereDate('deadline', '<', today());
    }
}
