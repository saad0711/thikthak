<?php

namespace App\Models;

use App\Models\Concerns\HasF13ProviderProfile;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Shared provider-only record. F13 adds profile/portfolio behaviour through
 * HasF13ProviderProfile; F14 and F17 can add their own traits later.
 */
class ProviderProfile extends Model
{
    use HasFactory, HasF13ProviderProfile;

    public const MAX_PORTFOLIO_ITEMS = 12;

    protected $fillable = [
        'user_id',
        'bio',
        'photo_path',
        'years_experience',
        'area_name',
        'latitude',
        'longitude',
        'radius_km',
    ];

    protected $casts = [
        'years_experience' => 'integer',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'radius_km' => 'integer',
    ];

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
