<?php

namespace App\Filament\Manager\Resources\AdoptionRequests\Pages;

use App\Filament\Manager\Resources\AdoptionRequests\AdoptionRequestResource;
use Filament\Resources\Pages\ListRecords;

class ListAdoptionRequests extends ListRecords
{
    protected static string $resource = AdoptionRequestResource::class;

    public function getSubheading(): ?string
    {
        return 'Kelola dan putuskan permohonan adopsi pohon yang diajukan oleh warga.';
    }
}
