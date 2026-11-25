<?php

namespace App\Filament\Citizen\Resources\TreeIssues\Pages;

use App\Filament\Citizen\Resources\TreeIssues\TreeIssueResource;
use App\Filament\Concerns\CreatesTreeIssue;
use Filament\Resources\Pages\CreateRecord;

class CreateTreeIssue extends CreateRecord
{
    use CreatesTreeIssue;

    protected static string $resource = TreeIssueResource::class;

    protected static ?string $title = 'Laporkan Masalah Pohon';

    protected static bool $canCreateAnother = false;

    protected function getRedirectUrl(): string
    {
        return TreeIssueResource::getUrl('index');
    }
}
