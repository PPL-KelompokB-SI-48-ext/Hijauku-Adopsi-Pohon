<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plot extends Model
{
    protected $fillable = ['site_id', 'code', 'capacity', 'status'];

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function trees(): HasMany
    {
        return $this->hasMany(Tree::class);
    }

    public function plantingBatches(): HasMany
    {
        return $this->hasMany(PlantingBatch::class);
    }
}
