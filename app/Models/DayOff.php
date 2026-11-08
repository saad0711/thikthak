<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DayOff extends Model
{
    protected $table = 'provider_days_off';

    protected $fillable = [
        'provider_profile_id',
        'date',
        'reason',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    /**
     * Format date-only columns as Y-m-d so SQLite stores them consistently with DATE queries.
     */
    public function fromDateTime($value)
    {
        $formatted = parent::fromDateTime($value);

        if (is_string($formatted) && str_ends_with($formatted, ' 00:00:00')) {
            return substr($formatted, 0, 10);
        }

        return $formatted;
    }

    public function providerProfile(): BelongsTo
    {
        return $this->belongsTo(ProviderProfile::class);
    }
}
