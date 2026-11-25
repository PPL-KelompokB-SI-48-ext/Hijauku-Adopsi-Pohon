<?php

namespace App\Filament\FieldStaff\Resources\TreeIssues\Pages;

use App\Filament\Concerns\CreatesTreeIssue;
use App\Filament\FieldStaff\Resources\TreeIssues\TreeIssueResource;
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
