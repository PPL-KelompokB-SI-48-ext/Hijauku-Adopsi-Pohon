<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AdoptionRequest extends Model
{
    protected $fillable = ['tree_id', 'requester_id', 'reason', 'status', 'reviewer_id', 'decision_reason'];

    public function tree(): BelongsTo
    {
        return $this->belongsTo(Tree::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function agreement(): HasOne
    {
        return $this->hasOne(AdoptionAgreement::class, 'request_id');
    }

    public function allocation(): HasOne
    {
        return $this->hasOne(TreeAdoptionAllocation::class, 'request_id');
    }
}
