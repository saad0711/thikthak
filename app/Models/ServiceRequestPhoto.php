<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * F4: one photo attached to a service request.
 */
#[Fillable(['path', 'sort_order'])]
class ServiceRequestPhoto extends Model
{
    /**
     * Delete the image file when the database row is deleted.
     */
    protected static function booted(): void
    {
        static::deleted(fn (self $photo) => Storage::disk('public')->delete($photo->path));
    }

    /**
     * @return BelongsTo<ServiceRequest, $this>
     */
    public function serviceRequest(): BelongsTo
    {
        return $this->belongsTo(ServiceRequest::class);
    }

    public function url(): string
    {
        return Storage::disk('public')->url($this->path);
    }
}
