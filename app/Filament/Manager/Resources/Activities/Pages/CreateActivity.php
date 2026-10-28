<?php

namespace App\Filament\Manager\Resources\Activities\Pages;

use App\Actions\Activities\CreateDraftActivity as CreateDraftActivityAction;
use App\Filament\Manager\Resources\Activities\ActivityResource;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateActivity extends CreateRecord
{
    protected static ?string $title = 'Tambah Kegiatan Lapangan';

    protected static ?string $breadcrumb = 'Tambah';

    protected static string $resource = ActivityResource::class;

    protected static bool $canCreateAnother = false;

    protected function handleRecordCreation(array $data): Model
    {
        $actor = auth()->user();

        abort_unless($actor instanceof User, 403);

        return app(CreateDraftActivityAction::class)->handle($actor, $data);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Kegiatan lapangan berhasil dicatat sebagai draf.';
    }

    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()->label('Simpan sebagai draf');
    }

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()->label('Batal');
    }
}

