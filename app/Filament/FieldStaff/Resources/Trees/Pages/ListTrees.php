<?php

namespace App\Filament\FieldStaff\Resources\Trees\Pages;

use App\Filament\FieldStaff\Resources\Trees\TreeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTrees extends ListRecords
{
    protected static string $resource = TreeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Catat Pohon Baru'),
        ];
    }

    public function getSubheading(): ?string
    {
        return 'Kelola dan pantau inventaris pohon yang terdata di lapangan.';
    }
}
