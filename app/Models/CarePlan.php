<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CarePlan extends Model
{
    protected $fillable = ['tree_id', 'assignment_id', 'planned_on', 'action', 'status'];

    protected function casts(): array
    {
        return ['planned_on' => 'date'];
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
}
