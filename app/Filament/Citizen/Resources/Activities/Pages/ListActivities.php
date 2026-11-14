<?php

namespace App\Filament\Citizen\Resources\Activities\Pages;

use App\Filament\Citizen\Resources\Activities\ActivityResource;
use Filament\Resources\Pages\ListRecords;

class ListActivities extends ListRecords
{
    protected static string $resource = ActivityResource::class;

    protected static ?string $title = 'Kegiatan Tersedia';

    protected function getHeaderActions(): array
    {
        return [];
    }
}
