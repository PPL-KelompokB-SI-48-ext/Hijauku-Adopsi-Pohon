<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Tree extends Model
{
    use HasFactory;

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

    public function adoptionAllocations(): HasMany
    {
        return $this->hasMany(TreeAdoptionAllocation::class);
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

    /**
     * Pengamatan pertumbuhan terverifikasi paling baru untuk pohon ini.
     */
    public function latestVerifiedObservation(): HasOne
    {
        return $this->hasOne(GrowthObservation::class)
            ->ofMany(
                ['observed_on' => 'max', 'id' => 'max'],
                fn (Builder $query): Builder => $query->where('status', 'verified')
            );
    }

    /**
     * Pohon yang lebih dari N hari (default 30 hari) belum memiliki pengamatan terverifikasi.
     */
    public function scopeUnmonitored(Builder $query, int $days = 30): Builder
    {
        $cutoffDate = now()->subDays($days)->toDateString();

        return $query->whereDoesntHave('growthObservations', function (Builder $observationQuery) use ($cutoffDate): void {
            $observationQuery
                ->where('status', 'verified')
                ->whereDate('observed_on', '>=', $cutoffDate);
        });
    }
}
