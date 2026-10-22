<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TreeIssue extends Model
{
    protected $fillable = ['tree_id', 'reporter_id', 'description', 'severity', 'evidence_path', 'status', 'resolution', 'reviewer_id'];

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
}
