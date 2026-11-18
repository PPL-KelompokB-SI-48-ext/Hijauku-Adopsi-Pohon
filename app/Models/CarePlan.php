<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class CarePlan extends Model
{
    protected $fillable = ['tree_id', 'assignment_id', 'planned_on', 'action', 'priority', 'status'];

    protected function casts(): array
    {
        return ['planned_on' => 'date'];
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query
            ->whereNotIn('status', ['cancelled', 'completed'])
            ->whereDate('planned_on', '<', now()->toDateString())
            ->whereDoesntHave('records', function (Builder $q) {
                $q->where('status', 'verified')->orWhereNotNull('verified_at');
            });
    }

    public function isOverdue(): bool
    {
        if (in_array($this->status, ['cancelled', 'completed'], true)) {
            return false;
        }

        if ($this->planned_on === null || $this->planned_on->isFuture() || $this->planned_on->isToday()) {
            return false;
        }

        return ! $this->records()
            ->where(function (Builder $query) {
                $query->where('status', 'verified')->orWhereNotNull('verified_at');
            })
            ->exists();
    }

    public function tree(): BelongsTo
    {
        return $this->belongsTo(Tree::class);
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(FieldAssignment::class, 'assignment_id');
    }

    public function records(): HasMany
    {
        return $this->hasMany(CareRecord::class, 'plan_id');
    }

    public function activityLogs(): MorphMany
    {
        return $this->morphMany(ActivityLog::class, 'subject');
    }
}
