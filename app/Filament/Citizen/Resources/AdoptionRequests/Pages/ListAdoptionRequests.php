<?php

namespace App\Filament\Citizen\Resources\AdoptionRequests\Pages;

use App\Filament\Citizen\Resources\AdoptionRequests\AdoptionRequestResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAdoptionRequests extends ListRecords
{
    protected static string $resource = AdoptionRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Ajukan Adopsi'),
        ];
    }

    public function getSubheading(): ?string
    {
        return 'Daftar permohonan adopsi pohon yang telah diajukan.';
    }
}