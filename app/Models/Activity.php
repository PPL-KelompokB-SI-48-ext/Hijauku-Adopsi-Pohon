<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Activity extends Model
{
    protected $fillable = ['site_id', 'title', 'kind', 'starts_at', 'ends_at', 'quota', 'status'];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'quota' => 'integer',
        ];
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function activityLogs(): MorphMany
    {
        return $this->morphMany(ActivityLog::class, 'subject');
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(ActivityRegistration::class);
    }

    public function fieldAssignments(): HasMany
    {
        return $this->hasMany(FieldAssignment::class);
    }

    public function galleryEntries(): HasMany
    {
        return $this->hasMany(GalleryEntry::class);
    }
}
