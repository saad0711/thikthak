<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

/**
 * Keeps a unique URL slug in sync with the model's name, e.g. "AC Repair" → "ac-repair".
 */
trait HasSlug
{
    protected static function bootHasSlug(): void
    {
        static::saving(function (self $model) {
            if ($model->isDirty('name') || blank($model->slug)) {
                $model->slug = static::uniqueSlugFor($model->name, $model->getKey());
            }
        });
    }

    protected static function uniqueSlugFor(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'item';
        $slug = $base;
        $suffix = 2;

        while (static::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }
}
