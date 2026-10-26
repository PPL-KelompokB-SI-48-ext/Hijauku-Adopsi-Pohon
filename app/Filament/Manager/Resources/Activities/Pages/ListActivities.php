<?php

namespace App\Filament\Manager\Resources\Activities\Pages;

use App\Filament\Manager\Resources\Activities\ActivityResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListActivities extends ListRecords
{
    protected static string $resource = ActivityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Tambah Kegiatan'),
        ];
    }
}

