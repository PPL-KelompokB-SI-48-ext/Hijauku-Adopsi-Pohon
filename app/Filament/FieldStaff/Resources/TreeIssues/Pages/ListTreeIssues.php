<?php

namespace App\Filament\FieldStaff\Resources\TreeIssues\Pages;

use App\Filament\FieldStaff\Resources\TreeIssues\TreeIssueResource;
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
        return 'Laporkan masalah pada pohon yang ditugaskan kepada Anda.';
    }
}
