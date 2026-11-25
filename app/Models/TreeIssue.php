<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class TreeIssue extends Model
{
    public const SEVERITY_OPTIONS = [
        'low' => 'Rendah',
        'normal' => 'Normal',
        'high' => 'Tinggi',
        'urgent' => 'Mendesak',
    ];

    protected $fillable = ['tree_id', 'reporter_id', 'description', 'severity', 'evidence_path', 'status', 'resolution', 'reviewer_id', 'assignee_id'];

    public function scopeReportedBy(Builder $query, User|int $reporter): Builder
    {
        $reporterId = $reporter instanceof User ? $reporter->getKey() : $reporter;

        return $query->where('reporter_id', $reporterId);
    }

    public function tree(): BelongsTo
    {
        return $this->belongsTo(Tree::class);
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function activityLogs(): MorphMany
    {
        return $this->morphMany(ActivityLog::class, 'subject');
    }
}
