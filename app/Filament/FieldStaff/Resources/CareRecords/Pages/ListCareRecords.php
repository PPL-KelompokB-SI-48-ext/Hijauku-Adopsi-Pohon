<?php

namespace App\Filament\FieldStaff\Resources\CareRecords\Pages;

use App\Filament\FieldStaff\Resources\CareRecords\CareRecordResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCareRecords extends ListRecords
{
    protected static string $resource = CareRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Catat Realisasi'),
        ];
    }

    public function getSubheading(): ?string
    {
        return 'Daftar tindakan perawatan pohon yang sudah Anda laksanakan di lapangan.';
    }
}
