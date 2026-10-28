<?php

namespace App\Filament\Manager\Resources\CarePlans\Pages;

use App\Filament\Manager\Resources\CarePlans\CarePlanResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCarePlans extends ListRecords
{
    protected static string $resource = CarePlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Tambah Rencana'),
        ];
    }

    public function getSubheading(): ?string
    {
        return 'Jadwal dan agenda teknis perawatan pohon berkala sebelum dieksekusi di lapangan.';
    }
}
