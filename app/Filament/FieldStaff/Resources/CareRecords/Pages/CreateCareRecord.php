<?php

namespace App\Filament\FieldStaff\Resources\CareRecords\Pages;

use App\Filament\FieldStaff\Resources\CareRecords\CareRecordResource;
use App\Models\CareRecord as CareRecordModel;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateCareRecord extends CreateRecord
{
    protected static string $resource = CareRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getTitle(): string
    {
        return 'Catat Realisasi Perawatan';
    }

    protected function handleRecordCreation(array $data): CareRecordModel
    {
        $actor = auth()->user();
        abort_unless($actor instanceof User, 403);

        return app(\App\Actions\CareRecords\CreateCareRecord::class)->handle($actor, $data);
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->title('Realisasi perawatan berhasil dicatat.')
            ->success();
    }

    protected function getRedirectUrl(): string
    {
        return CareRecordResource::getUrl('index');
    }
}
