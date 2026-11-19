<?php

namespace App\Filament\Citizen\Resources\Registrations\Pages;

use App\Filament\Citizen\Resources\Registrations\RegistrationResource;
use Filament\Resources\Pages\ListRecords;

class ListRegistrations extends ListRecords
{
    protected static string $resource = RegistrationResource::class;

    protected static ?string $title = 'Pendaftaran Saya';

    protected function getHeaderActions(): array
    {
        return [];
    }
}
