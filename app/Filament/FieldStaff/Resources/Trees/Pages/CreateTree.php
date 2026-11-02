<?php

namespace App\Filament\FieldStaff\Resources\Trees\Pages;

use App\Filament\FieldStaff\Resources\Trees\TreeResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreateTree extends CreateRecord
{
    protected static ?string $title = 'Catat Pohon Baru';

    protected static ?string $breadcrumb = 'Catat Pohon';

    protected static string $resource = TreeResource::class;

    protected static bool $canCreateAnother = false;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Pohon berhasil dicatat ke dalam inventaris.';
    }

    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()->label('Simpan Pohon');
    }

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()->label('Batal');
    }
}
