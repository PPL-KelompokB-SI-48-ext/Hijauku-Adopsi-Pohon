<?php

namespace App\Filament\Manager\Resources\FieldAssignments\Pages;

use App\Filament\Manager\Resources\FieldAssignments\FieldAssignmentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFieldAssignments extends ListRecords
{
    protected static string $resource = FieldAssignmentResource::class;

    protected static ?string $title = 'Penugasan Lapangan';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tetapkan Petugas')
                ->icon('heroicon-o-plus'),
        ];
    }
}
