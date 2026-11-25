<?php

namespace App\Filament\Citizen\Resources\TreeIssues\Pages;

use App\Filament\Citizen\Resources\TreeIssues\TreeIssueResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTreeIssues extends ListRecords
{
    protected static string $resource = TreeIssueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Laporkan Masalah'),
        ];
    }

    public function getSubheading(): ?string
    {
        return 'Laporkan masalah pada pohon adopsi Anda dan pantau statusnya.';
    }
}
