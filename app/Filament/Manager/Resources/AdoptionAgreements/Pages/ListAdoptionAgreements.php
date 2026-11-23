<?php

namespace App\Filament\Manager\Resources\AdoptionAgreements\Pages;

use App\Filament\Manager\Resources\AdoptionAgreements\AdoptionAgreementResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAdoptionAgreements extends ListRecords
{
    protected static string $resource = AdoptionAgreementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Buat Kesepakatan'),
        ];
    }

    public function getSubheading(): ?string
    {
        return 'Kelola komitmen dan periode adopsi pohon.';
    }

    public function getBreadcrumbs(): array
    {
        return ['Adopsi', 'Kesepakatan Adopsi'];
    }
}
