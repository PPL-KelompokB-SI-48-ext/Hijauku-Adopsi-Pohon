<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Tree extends Model
{
    protected $fillable = ['species_id', 'plot_id', 'batch_id', 'code', 'planted_on', 'initial_condition'];

    protected function casts(): array
    {
        return ['planted_on' => 'date'];
    }

    public function species(): BelongsTo
    {
        return $this->belongsTo(TreeSpecies::class, 'species_id');
    }

    public function plot(): BelongsTo
    {
        return $this->belongsTo(Plot::class);
    }

    public function plantingBatch(): BelongsTo
    {
        return $this->belongsTo(PlantingBatch::class, 'batch_id');
    }

    public function adoptionRequests(): HasMany
    {
        return $this->hasMany(AdoptionRequest::class);
    }

    public function adoptionAllocation(): HasOne
    {
        return $this->hasOne(TreeAdoptionAllocation::class);
    }

    public function issues(): HasMany
    {
        return $this->hasMany(TreeIssue::class);
    }

    public function carePlans(): HasMany
    {
        return $this->hasMany(CarePlan::class);
    }

    public function careRecords(): HasMany
    {
        return $this->hasMany(CareRecord::class);
    }

    public function growthObservations(): HasMany
    {
        return $this->hasMany(GrowthObservation::class);
    }
}
