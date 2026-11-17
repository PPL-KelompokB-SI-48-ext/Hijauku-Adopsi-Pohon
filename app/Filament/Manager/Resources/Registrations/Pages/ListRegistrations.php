<?php

namespace App\Filament\Manager\Resources\Registrations\Pages;

use App\Filament\Manager\Resources\Registrations\RegistrationResource;
use Filament\Resources\Pages\ListRecords;

class ListRegistrations extends ListRecords
{
    protected static string $resource = RegistrationResource::class;

    protected static ?string $title = 'Kehadiran Peserta Kegiatan';

    protected function getHeaderActions(): array
    {
        return [];
    }
}
