<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GreenActionProposal extends Model
{
    protected $fillable = ['proposer_id', 'title', 'site_description', 'proposed_on', 'participants_estimate', 'description', 'status', 'reviewer_id', 'decision_reason'];

    protected function casts(): array
    {
        return ['proposed_on' => 'date'];
    }

    public function proposer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'proposer_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }
}
