<?php

namespace App\Models;

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
    use HasFactory;

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
