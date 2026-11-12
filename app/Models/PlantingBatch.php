<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class PlantingBatch extends Model
{
    use HasFactory;

    protected $fillable = ['plot_id', 'code', 'planted_on', 'notes'];

    protected function casts(): array
    {
        return ['planted_on' => 'date'];
    }

    public function plot(): BelongsTo
    {
        return $this->belongsTo(Plot::class);
    }

    public function trees(): HasMany
    {
        return $this->hasMany(Tree::class, 'batch_id');
    }

    public function activityLogs(): MorphMany
    {
        return $this->morphMany(ActivityLog::class, 'subject');
    }
}
