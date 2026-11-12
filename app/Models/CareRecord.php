<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class CareRecord extends Model
{
    protected $fillable = ['tree_id', 'plan_id', 'performed_by', 'performed_on', 'action', 'photo_path', 'status', 'verified_by', 'verified_at', 'review_note'];

    protected function casts(): array
    {
        return ['performed_on' => 'date', 'verified_at' => 'datetime'];
    }

    public function tree(): BelongsTo
    {
        return $this->belongsTo(Tree::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(CarePlan::class, 'plan_id');
    }

    public function performer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function activityLogs(): MorphMany
    {
        return $this->morphMany(ActivityLog::class, 'subject');
    }

    /**
     * Scope: only records belonging to a specific field staff actor.
     */
    public function scopeForPerformer(Builder $query, User|int $user): Builder
    {
        $id = $user instanceof User ? $user->getKey() : $user;

        return $query->where('performed_by', $id);
    }
}
