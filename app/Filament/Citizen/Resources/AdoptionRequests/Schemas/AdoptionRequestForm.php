<?php

namespace App\Filament\Citizen\Resources\AdoptionRequests\Schemas;

use App\Models\AdoptionRequest;
use App\Models\Tree;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class AdoptionRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        $userId = auth()->id();

        $activeTreeIds = AdoptionRequest::query()
            ->where('requester_id', $userId)
            ->whereIn('status', ['draft', 'submitted'])
            ->pluck('tree_id')
            ->all();

        return $schema->components([
            Select::make('tree_id')
                ->label('Pohon')
                ->helperText(
                    'Pilih pohon yang tersedia dan belum memiliki permohonan aktif dari akun Anda.'
                )
                ->options(
                    Tree::query()
                        ->whereDoesntHave('adoptionAllocation')
                        ->whereNotIn('id', $activeTreeIds)
                        ->with('species')
                        ->get()
                        ->mapWithKeys(fn (Tree $tree) => [
                            $tree->id => $tree->code . ' - ' . $tree->species->name,
                        ])
                        ->all()
                )
                ->searchable()
                ->required(),

            Textarea::make('reason')
                ->label('Alasan Adopsi')
                ->placeholder('Tuliskan alasan Anda ingin mengadopsi pohon ini.')
                ->required()
                ->maxLength(1000),
        ]);
    }
}