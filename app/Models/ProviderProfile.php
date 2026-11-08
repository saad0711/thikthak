<?php

namespace App\Models;

use App\Models\Concerns\HasAvailability;
use App\Models\Concerns\HasF13ProviderProfile;
use Database\Factories\ProviderProfileFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Shared provider-only record. F13 adds profile/portfolio behaviour through
 * HasF13ProviderProfile and F14 adds availability through HasAvailability;
 * F17 can add its own trait later.
 */
class ProviderProfile extends Model
{
    /** @use HasFactory<ProviderProfileFactory> */
    use HasAvailability, HasF13ProviderProfile, HasFactory;

    public const MAX_PORTFOLIO_ITEMS = 12;

    public const MIN_COMPLETENESS_TO_GO_ONLINE = 60;

    protected $fillable = [
        'user_id',
        'bio',
        'photo_path',
        'years_experience',
        'area_name',
        'latitude',
        'longitude',
        'radius_km',
        'is_online',
        'slot_minutes',
    ];

    protected function casts(): array
    {
        return [
            'is_online' => 'boolean',
            'slot_minutes' => 'integer',
            'years_experience' => 'integer',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'radius_km' => 'integer',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
