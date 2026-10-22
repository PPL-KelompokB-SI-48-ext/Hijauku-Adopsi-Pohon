<?php

namespace App\Filament\Manager\Resources\Programs\Pages;

use App\Filament\Manager\Resources\Programs\ProgramResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPrograms extends ListRecords
{
    protected static string $resource = ProgramResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Tambah Program'),
        ];
    }

    public function getSubheading(): ?string
    {
        return 'Kelola program penghijauan dan periode pelaksanaannya.';
    }
}
