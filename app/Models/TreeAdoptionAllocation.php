<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TreeAdoptionAllocation extends Model
{
    public const VALID_STATUSES = [
        'awaiting_acceptance',
        'active',
    ];

    protected $fillable = [
        'tree_id',
        'request_id',
        'agreement_id',
        'status',
        'reserved_at',
    ];

    protected function casts(): array
    {
        return [
            'reserved_at' => 'datetime',
        ];
    }

    public function tree(): BelongsTo
    {
        return $this->belongsTo(Tree::class);
    }

    public function adoptionRequest(): BelongsTo
    {
        return $this->belongsTo(AdoptionRequest::class, 'request_id');
    }

    public function agreement(): BelongsTo
    {
        return $this->belongsTo(AdoptionAgreement::class, 'agreement_id');
    }
}
