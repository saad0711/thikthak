<?php

namespace App\Models;

use App\Models\Concerns\HasAvailability;
use Database\Factories\ProviderProfileFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Provider-only data, one row per provider.
 *
 * This is the shared base. F13 (profile), F14 (availability) and F17
 * (verification) each add their own columns, and their methods in their own traits.
 */
class ProviderProfile extends Model
{
    /** @use HasFactory<ProviderProfileFactory> */
    use HasFactory, HasAvailability;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'is_online' => 'boolean',
            'slot_minutes' => 'integer',
            'completeness_percentage' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Completeness check from F13 (Farhaan).
     * canGoOnline() requires at least 60% profile completeness.
     */
    public function canGoOnline(): bool
    {
        return ($this->completeness_percentage ?? 100) >= 60;
    }
}
