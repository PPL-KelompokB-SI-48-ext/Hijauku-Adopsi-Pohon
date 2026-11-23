<?php

namespace App\Filament\FieldStaff\Resources\GrowthObservations\Pages;

use App\Filament\FieldStaff\Resources\GrowthObservations\GrowthObservationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListGrowthObservations extends ListRecords
{
    protected static string $resource = GrowthObservationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Catat Pengamatan'),
        ];
    }

    public function getSubheading(): ?string
    {
        return 'Daftar catatan pengukuran tinggi, diameter batang, dan kondisi fisik pohon.';
    }
}
