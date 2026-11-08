<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PortfolioItem extends Model
{
    protected $fillable = ['image_path', 'caption'];

    // The profile this portfolio image belongs to.
    public function providerProfile(): BelongsTo
    {
        return $this->belongsTo(ProviderProfile::class);
    }

    // Web address of the image.
    public function imageUrl(): string
    {
        return asset('storage/' . $this->image_path);
    }
}
