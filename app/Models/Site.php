<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Site extends Model
{
    protected $fillable = ['program_id', 'name', 'address', 'latitude', 'longitude'];

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function plots(): HasMany
    {
        return $this->hasMany(Plot::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }
}
