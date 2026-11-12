<?php

namespace App\Filament\FieldStaff\Resources\PlantingBatches\Pages;

use App\Filament\FieldStaff\Resources\PlantingBatches\PlantingBatchResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPlantingBatches extends ListRecords
{
    protected static string $resource = PlantingBatchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Tambah Kelompok Tanam'),
        ];
    }

    public function getSubheading(): ?string
    {
        return 'Daftar riwayat pelaksanaan penanaman pohon pada petak.';
    }
}

