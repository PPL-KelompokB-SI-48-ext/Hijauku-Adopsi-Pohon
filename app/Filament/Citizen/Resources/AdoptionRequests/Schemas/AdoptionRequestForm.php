<?php

namespace App\Filament\Citizen\Resources\AdoptionRequests\Schemas;

use App\Models\Tree;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class AdoptionRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('tree_id')
                    ->label('Pohon')
                    ->options(
                        Tree::query()
                            ->whereDoesntHave('adoptionAllocation')
                            ->with('species')
                            ->get()
                            ->mapWithKeys(fn (Tree $tree) => [
                                $tree->id => $tree->code . ' - ' . $tree->species->name,
                            ])
                    )
                    ->searchable()
                    ->required(),

                Textarea::make('reason')
                    ->label('Alasan Adopsi')
                    ->required()
                    ->maxLength(1000),
            ]);
    }
}