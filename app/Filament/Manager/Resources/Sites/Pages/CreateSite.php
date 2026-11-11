<?php

namespace App\Filament\Manager\Resources\Sites\Pages;

use App\Actions\Sites\CreateSite as CreateSiteAction;
use App\Filament\Manager\Resources\Sites\SiteResource;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateSite extends CreateRecord
{
    protected static ?string $title = 'Tambah Lokasi Tanam';

    protected static ?string $breadcrumb = 'Tambah';

    protected static string $resource = SiteResource::class;

    protected static bool $canCreateAnother = false;

    protected function handleRecordCreation(array $data): Model
    {
        $actor = auth()->user();
        abort_unless($actor instanceof User, 403);

        return app(CreateSiteAction::class)->handle($actor, $data);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Lokasi tanam berhasil dicatat.';
    }

    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()->label('Simpan lokasi');
    }

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()->label('Batal');
    }
}
