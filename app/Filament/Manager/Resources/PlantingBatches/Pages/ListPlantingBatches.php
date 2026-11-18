<?php

namespace App\Filament\Manager\Resources\PlantingBatches\Pages;

use App\Filament\Manager\Resources\PlantingBatches\PlantingBatchResource;
use Filament\Resources\Pages\ListRecords;

class ListPlantingBatches extends ListRecords
{
    protected static string $resource = PlantingBatchResource::class;

    protected static ?string $title = 'Kelompok Penanaman';
}
