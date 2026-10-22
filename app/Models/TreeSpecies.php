<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TreeSpecies extends Model
{
    protected $fillable = ['name', 'care_notes', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function trees(): HasMany
    {
        return $this->hasMany(Tree::class, 'species_id');
    }
}
