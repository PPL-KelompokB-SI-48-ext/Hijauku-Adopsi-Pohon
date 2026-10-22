<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AdoptionAgreement extends Model
{
    protected $fillable = ['request_id', 'tree_id', 'adopter_id', 'terms', 'starts_on', 'ends_on', 'status', 'accepted_at'];

    protected function casts(): array
    {
        return [
            'starts_on' => 'date',
            'ends_on' => 'date',
            'accepted_at' => 'datetime',
        ];
    }

    public function adoptionRequest(): BelongsTo
    {
        return $this->belongsTo(AdoptionRequest::class, 'request_id');
    }

    public function tree(): BelongsTo
    {
        return $this->belongsTo(Tree::class);
    }

    public function adopter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'adopter_id');
    }

    public function allocation(): HasOne
    {
        return $this->hasOne(TreeAdoptionAllocation::class, 'agreement_id');
    }
}
