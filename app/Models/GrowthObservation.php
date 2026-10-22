<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GrowthObservation extends Model
{
    protected $fillable = ['tree_id', 'observed_by', 'observed_on', 'height_cm', 'diameter_cm', 'condition', 'photo_path', 'status', 'verified_by', 'verified_at', 'review_note'];

    protected function casts(): array
    {
        return ['observed_on' => 'date', 'verified_at' => 'datetime'];
    }

    public function tree(): BelongsTo
    {
        return $this->belongsTo(Tree::class);
    }

    public function observer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'observed_by');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
