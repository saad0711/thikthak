<?php

namespace App\Models\Concerns;

use App\Models\Certification;
use App\Models\PortfolioItem;
use App\Models\Service;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Feature 13 relationships and profile-completeness helpers.
 * This trait is added to the shared ProviderProfile model; it does not
 * replace the model or its existing user() relationship.
 */
trait HasF13ProviderProfile
{
    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'provider_service')
            ->withPivot('price')
            ->withTimestamps();
    }

    public function certifications(): HasMany
    {
        return $this->hasMany(Certification::class);
    }

    public function portfolioItems(): HasMany
    {
        return $this->hasMany(PortfolioItem::class);
    }

    public function photoUrl(): ?string
    {
        return $this->photo_path ? asset('storage/' . $this->photo_path) : null;
    }

    public function hasServiceArea(): bool
    {
        return filled($this->area_name)
            && $this->latitude !== null
            && $this->longitude !== null
            && $this->radius_km !== null;
    }

    /**
     * This 100-point weighting is an implementation choice for F13;
     * the feature list requires a completeness score but does not prescribe weights.
     */
    public function checklist(): array
    {
        return [
            ['label' => 'Add a profile photo', 'points' => 15, 'done' => filled($this->photo_path)],
            ['label' => 'Write your bio', 'points' => 15, 'done' => filled($this->bio)],
            ['label' => 'Add at least 1 service with a price', 'points' => 25, 'done' => $this->services()->exists()],
            ['label' => 'Set your service area', 'points' => 20, 'done' => $this->hasServiceArea()],
            ['label' => 'Add at least 1 certification', 'points' => 10, 'done' => $this->certifications()->exists()],
            ['label' => 'Upload at least 3 portfolio images', 'points' => 15, 'done' => $this->portfolioItems()->count() >= 3],
        ];
    }

    public function completeness(): int
    {
        $score = 0;
        foreach ($this->checklist() as $item) {
            if ($item['done']) {
                $score += $item['points'];
            }
        }
        return $score;
    }

    public function missingItems(): array
    {
        $missing = [];
        foreach ($this->checklist() as $item) {
            if (! $item['done']) {
                $missing[] = $item['label'] . ' (+' . $item['points'] . '%)';
            }
        }
        return $missing;
    }
}
