<?php

namespace App\Filament\Manager\Resources\TreeSpecies\Pages;

use App\Filament\Manager\Resources\TreeSpecies\TreeSpeciesResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTreeSpecies extends ListRecords
{
    protected static string $resource = TreeSpeciesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Tambah Jenis Pohon'),
        ];
    }

    public function getSubheading(): ?string
    {
        return 'Kelola katalog acuan jenis pohon dan panduan perawatannya.';
    }
}
