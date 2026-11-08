<?php

namespace App\Models;

use App\Models\Concerns\HasSlug;
use App\Support\Money;
use Database\Factories\ServiceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * F1: a bookable service inside a category, with an indicative BDT price range.
 */
#[Fillable(['service_category_id', 'name', 'description', 'price_min', 'price_max', 'is_active', 'sort_order'])]
class Service extends Model
{
    /** @use HasFactory<ServiceFactory> */
    use HasFactory, HasSlug;

    /** Sort options the catalog search accepts, mapped to their labels. */
    public const SORTS = [
        'name_asc' => 'Name (A–Z)',
        'name_desc' => 'Name (Z–A)',
        'price_asc' => 'Price (low to high)',
        'price_desc' => 'Price (high to low)',
    ];

    protected function casts(): array
    {
        return [
            'price_min' => 'decimal:2',
            'price_max' => 'decimal:2',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<ServiceCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }

    /**
     * @return HasMany<ServiceRequest, $this>
     */
    public function serviceRequests(): HasMany
    {
        return $this->hasMany(ServiceRequest::class);
    }

    public function priceRangeLabel(): string
    {
        return Money::range($this->price_min, $this->price_max);
    }

    /**
     * Business rule: a service that customers have already requested can't be deleted.
     */
    public function canBeDeleted(): bool
    {
        return ! $this->serviceRequests()->exists();
    }

    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('services.is_active', true);
    }

    /**
     * What customers may see and request: the service and its category are both active.
     */
    #[Scope]
    protected function available(Builder $query): void
    {
        $query->where('services.is_active', true)
            ->whereHas('category', fn (Builder $category) => $category->where('is_active', true));
    }

    /**
     * Keyword search over the service's and its category's name and description.
     *
     * Matches at the start of a word, so "ac" finds "AC servicing" but not "replace".
     * Uses query bindings, so input like "' OR 1=1 --" is treated as plain text.
     */
    #[Scope]
    protected function search(Builder $query, ?string $term): void
    {
        $term = trim((string) $term);

        if ($term === '') {
            return;
        }

        $escaped = addcslashes($term, '%_\\');
        $patterns = [$escaped.'%', '% '.$escaped.'%'];

        $matchesWordStart = function (Builder $q, string $column) use ($patterns) {
            foreach ($patterns as $pattern) {
                $q->orWhere($column, 'like', $pattern);
            }
        };

        $query->where(function (Builder $q) use ($matchesWordStart) {
            $matchesWordStart($q, 'services.name');
            $matchesWordStart($q, 'services.description');
            $q->orWhereHas('category', fn (Builder $category) => $category->where(function (Builder $c) use ($matchesWordStart) {
                $matchesWordStart($c, 'name');
                $matchesWordStart($c, 'description');
            }));
        });
    }

    #[Scope]
    protected function inCategory(Builder $query, ?int $categoryId): void
    {
        $query->when($categoryId, fn (Builder $q) => $q->where('service_category_id', $categoryId));
    }

    /**
     * A service matches when its price range overlaps the filter range:
     * price_min ≤ filter max AND price_max ≥ filter min.
     */
    #[Scope]
    protected function priceBetween(Builder $query, int|float|string|null $min, int|float|string|null $max): void
    {
        if ($max !== null && $max !== '') {
            $query->where('price_min', '<=', $max);
        }

        if ($min !== null && $min !== '') {
            $query->where('price_max', '>=', $min);
        }
    }

    #[Scope]
    protected function sortedBy(Builder $query, ?string $sort): void
    {
        match ($sort) {
            'name_desc' => $query->orderByDesc('services.name'),
            'price_asc' => $query->orderBy('price_min')->orderBy('services.name'),
            'price_desc' => $query->orderByDesc('price_max')->orderBy('services.name'),
            default => $query->orderBy('services.name'),
        };
    }

    #[Scope]
    protected function ordered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderBy('name');
    }
}
