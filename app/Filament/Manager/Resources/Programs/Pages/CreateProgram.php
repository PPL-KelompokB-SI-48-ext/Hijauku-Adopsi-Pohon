<?php

namespace App\Filament\Manager\Resources\Programs\Pages;

use App\Actions\Programs\CreateDraftProgram as CreateDraftProgramAction;
use App\Filament\Manager\Resources\Programs\ProgramResource;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateProgram extends CreateRecord
{
    protected static ?string $title = 'Tambah Program Penghijauan';

    protected static ?string $breadcrumb = 'Tambah';

    protected static string $resource = ProgramResource::class;

    protected static bool $canCreateAnother = false;

    protected function handleRecordCreation(array $data): Model
    {
        $actor = auth()->user();

        abort_unless($actor instanceof User, 403);

        return app(CreateDraftProgramAction::class)->handle($actor, $data);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Program berhasil dicatat sebagai draf.';
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
