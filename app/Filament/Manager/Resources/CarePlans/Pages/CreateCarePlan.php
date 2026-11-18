<?php

namespace App\Filament\Manager\Resources\CarePlans\Pages;

use App\Actions\CarePlans\CreateCarePlan as CreateCarePlanAction;
use App\Filament\Manager\Resources\CarePlans\CarePlanResource;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateCarePlan extends CreateRecord
{
    protected static ?string $title = 'Tambah Rencana Perawatan';

    protected static ?string $breadcrumb = 'Tambah';

    protected static string $resource = CarePlanResource::class;

    protected static bool $canCreateAnother = false;

    protected function handleRecordCreation(array $data): Model
    {
        $actor = auth()->user();

        abort_unless($actor instanceof User, 403);

        return app(CreateCarePlanAction::class)->handle($actor, $data);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Rencana perawatan berhasil dicatat sebagai draf.';
    }

    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()->label('Simpan Rencana');
    }

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()->label('Batal');
    }
}
