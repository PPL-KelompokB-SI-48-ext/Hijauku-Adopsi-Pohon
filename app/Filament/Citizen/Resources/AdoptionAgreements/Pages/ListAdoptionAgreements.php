<?php

namespace App\Filament\Citizen\Resources\AdoptionAgreements\Pages;

use App\Filament\Citizen\Resources\AdoptionAgreements\AdoptionAgreementResource;
use Filament\Resources\Pages\ListRecords;

class ListAdoptionAgreements extends ListRecords
{
    protected static string $resource = AdoptionAgreementResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
